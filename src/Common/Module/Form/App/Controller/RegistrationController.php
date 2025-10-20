<?php

namespace UserSpace\Common\Module\Form\App\Controller;

use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\SecurityHelper;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\User\UserApiInterface;

class RegistrationController extends AbstractController
{
    /**
     * Поля, которые относятся к основной таблице wp_users.
     * @var string[]
     */
    private array $coreUserFields = ['user_login', 'user_email', 'user_pass'];

    public function __construct(
        private readonly FormManager            $formManager,
        private readonly FormFactory            $formFactory,
        private readonly SecurityHelper         $securityHelper,
        private readonly StringFilterInterface  $str,
        private readonly OptionManagerInterface $optionManager,
        private readonly UserApiInterface       $userApi
    )
    {
    }

    #[Route(path: '/register', method: 'POST')]
    public function handleRegistration(Request $request): JsonResponse
    {
        if ($this->userApi->isUserLoggedIn()) {
            return $this->error(['message' => $this->str->translate('You are already registered and logged in.')], 403);
        }

        $formType = 'registration';
        $config = $this->formManager->load($formType);

        if (null === $config) {
            return $this->error(['message' => $this->str->translate('Registration form configuration not found.')], 500);
        }

        // Обновляем DTO данными из запроса, не пересобирая его
        $fields = $config->getFields();
        foreach (array_keys($fields) as $fieldName) {
            $postValue = $request->getPost($fieldName);
            if ($postValue !== null) {
                // Санация будет происходить внутри объектов полей при валидации
                $config->updateFieldValue($fieldName, $this->str->unslash($postValue));
            }
        }

        $form = $this->formFactory->create($config);

        if (!$form->validate()) {
            return $this->error(['message' => $this->str->translate('Validation error.'), 'errors' => $form->getErrors()], 422);
        }

        $userData = [];
        $metaData = [];

        foreach ($form->getFields() as $field) {
            $fieldName = $field->getName();
            $fieldValue = $field->getValue();

            if (in_array($fieldName, $this->coreUserFields, true)) {
                $userData[$fieldName] = $fieldValue;
            } else {
                $metaData[$fieldName] = $fieldValue;
            }
        }

        $settings = $this->optionManager->get('usp_settings', []);
        $requireConfirmation = !empty($settings['require_email_confirmation']);

        if ($requireConfirmation) {
            // Регистрация с подтверждением
            $userId = $this->userApi->insertUser($userData);
            if (is_wp_error($userId)) {
                return $this->error(['message' => $userId->get_error_message()], 409);
            }

            // Устанавливаем временную роль
            $this->userApi->updateUser(['ID' => $userId, 'role' => 'need-confirm']);

            // Сохраняем мета-данные
            foreach ($metaData as $key => $value) {
                $this->userApi->updateUserMeta($userId, $key, $value);
            }

            $this->sendConfirmationEmail($userId, $userData);

            return $this->success(['message' => $this->str->translate('Registration successful! Please check your email to activate your account.')]);
        } else {
            // Регистрация без подтверждения
            $userId = $this->userApi->createUser($userData['user_login'], $userData['user_pass'], $userData['user_email']);
            if (is_wp_error($userId)) {
                return $this->error(['message' => $userId->get_error_message()], 409);
            }

            // Сохраняем мета-данные
            foreach ($metaData as $key => $value) {
                $this->userApi->updateUserMeta($userId, $key, $value);
            }

            return $this->success(['message' => $this->str->translate('Registration successful!')]);
        }
    }

    #[Route(path: '/confirm-registration', method: 'GET')]
    public function confirmRegistration(Request $request): void
    {
        $token = $request->getQuery('token', '');
        $data = json_decode(base64_decode($token), true);

        if (empty($data) || !is_array($data) || count($data) !== 3) {
            wp_safe_redirect(home_url() . '?reg-error=invalid_token');
            exit;
        }

        [$userLogin, $userIdHash, $securityHash] = $data;

        $user = $this->userApi->getUserBy('login', $userLogin);

        if (!$user || md5($user->ID) !== $userIdHash || md5($this->securityHelper->getSecurityKey() . $user->ID) !== $securityHash) {
            wp_safe_redirect(home_url() . '?reg-error=invalid_token');
            exit;
        }

        // Активируем пользователя, устанавливая ему роль по умолчанию
        $this->userApi->updateUser(['ID' => $user->ID, 'role' => $this->optionManager->get('default_role')]);

        // Перенаправляем на страницу входа с сообщением об успехе
        $settings = $this->optionManager->get('usp_settings', []);
        $loginPageUrl = !empty($settings['login_page_id']) ? get_permalink($settings['login_page_id']) : wp_login_url();
        wp_safe_redirect(add_query_arg('reg-success', 'confirmed', $loginPageUrl));
        exit;
    }

    private function sendConfirmationEmail(int $userId, array $userData): void
    {
        $tokenData = [
            $userData['user_login'],
            md5($userId),
            md5($this->securityHelper->getSecurityKey() . $userId)
        ];
        $token = base64_encode(json_encode($tokenData));

        $confirmationUrl = add_query_arg(['token' => $token], rest_url(USERSPACE_REST_NAMESPACE . '/confirm-registration'));

        $subject = sprintf($this->str->translate('[%s] Activate Your Account'), get_bloginfo('name'));
        $message = sprintf($this->str->translate("Thanks for signing up! To activate your account, please click this link:\n\n%s"), $confirmationUrl);

        wp_mail($userData['user_email'], $subject, $message);
    }
}