<?php

namespace UserSpace\Common\Module\User\App\UseCase\Registration;

use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueDispatcher;
use UserSpace\Common\Module\User\App\Task\Message\SendConfirmationEmailMessage;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\SecurityHelper;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\User\UserApiInterface;

class RegisterUserUseCase
{
    /**
     * Поля, которые относятся к основной таблице wp_users.
     * @var string[]
     */
    private const CORE_USER_FIELDS = ['user_login', 'user_email', 'user_pass'];

    public function __construct(
        private readonly FormManager            $formManager,
        private readonly FormFactory            $formFactory,
        private readonly SecurityHelper         $securityHelper,
        private readonly StringFilterInterface  $str,
        private readonly OptionManagerInterface $optionManager,
        private readonly UserApiInterface       $userApi,
        private readonly QueueDispatcher        $queueDispatcher
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(RegisterUserCommand $command): RegisterUserResult
    {
        if ($this->userApi->isUserLoggedIn()) {
            throw new UspException($this->str->translate('You are already registered and logged in.'), 403);
        }

        $config = $this->formManager->load($command->formType);
        if (null === $config) {
            throw new UspException($this->str->translate('Registration form configuration not found.'), 500);
        }

        // Обновляем DTO данными из запроса
        $fields = $config->getFields();
        foreach (array_keys($fields) as $fieldName) {
            /** @todo передавать через команду понятные параметры */
            if (array_key_exists($fieldName, $command->requestData)) {
                $config->updateFieldValue($fieldName, $this->str->unslash($command->requestData[$fieldName]));
            }
        }

        $form = $this->formFactory->create($config);
        if (!$form->validate()) {
            throw new UspException(
                $this->str->translate('Validation error.'),
                422,
                ['errors' => $form->getErrors()]
            );
        }

        $userData = [];
        $metaData = [];
        foreach ($form->getFields() as $field) {
            if (in_array($field->getName(), self::CORE_USER_FIELDS, true)) {
                $userData[$field->getName()] = $field->getValue();
            } else {
                $metaData[$field->getName()] = $field->getValue();
            }
        }

        $settings = $this->optionManager->get('usp_settings', []);
        $requireConfirmation = !empty($settings['require_email_confirmation']);

        if ($requireConfirmation) {
            return $this->registerWithConfirmation($userData, $metaData);
        }

        return $this->registerWithoutConfirmation($userData, $metaData);
    }

    /**
     * @throws UspException
     */
    private function registerWithConfirmation(array $userData, array $metaData): RegisterUserResult
    {
        $userId = $this->userApi->insertUser($userData);
        if (is_wp_error($userId)) {
            throw new UspException($userId->get_error_message(), 409);
        }

        $this->userApi->updateUser(['ID' => $userId, 'role' => 'need-confirm']);

        foreach ($metaData as $key => $value) {
            $this->userApi->updateUserMeta($userId, $key, $value);
        }

        $this->sendConfirmationEmail($userId, $userData);

        return new RegisterUserResult($this->str->translate('Registration successful! Please check your email to activate your account.'));
    }

    /**
     * @throws UspException
     */
    private function registerWithoutConfirmation(array $userData, array $metaData): RegisterUserResult
    {
        $userId = $this->userApi->createUser($userData['user_login'], $userData['user_pass'], $userData['user_email']);
        if (is_wp_error($userId)) {
            throw new UspException($userId->get_error_message(), 409);
        }

        foreach ($metaData as $key => $value) {
            $this->userApi->updateUserMeta($userId, $key, $value);
        }

        return new RegisterUserResult($this->str->translate('Registration successful!'));
    }

    private function sendConfirmationEmail(int $userId, array $userData): void
    {
        $tokenData = [
            $userData['user_login'],
            md5($userId),
            md5($this->securityHelper->getSecurityKey() . $userId)
        ];
        $token = base64_encode(json_encode($tokenData));

        $confirmationUrl = add_query_arg(['token' => $token], rest_url(USERSPACE_REST_NAMESPACE . '/user/confirm-registration'));

        $subject = sprintf($this->str->translate('[%s] Activate Your Account'), get_bloginfo('name'));
        $message = sprintf($this->str->translate("Thanks for signing up! To activate your account, please click this link:\n\n%s"), $confirmationUrl);

        $this->queueDispatcher->dispatch(new SendConfirmationEmailMessage($userData['user_email'], $subject, $message));
    }
}