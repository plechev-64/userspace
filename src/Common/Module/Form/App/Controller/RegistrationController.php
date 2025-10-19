<?php

namespace UserSpace\Common\Module\Form\App\Controller;

use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\SecurityHelper;

class RegistrationController extends AbstractController
{
    /**
     * Поля, которые относятся к основной таблице wp_users.
     * @var string[]
     */
    private array $coreUserFields = ['user_login', 'user_email', 'user_pass'];

    public function __construct(
        private readonly FormManager $formManager,
        private readonly FormFactory $formFactory,
        private readonly SecurityHelper $securityHelper
    ) {
    }

    #[Route(path: '/register', method: 'POST')]
    public function handleRegistration(Request $request): JsonResponse
    {
        if (is_user_logged_in()) {
            return $this->error(['message' => __('You are already registered and logged in.', 'usp')], 403);
        }

        $formType = 'registration';
        $config = $this->formManager->load($formType);

        if (null === $config) {
            return $this->error(['message' => __('Registration form configuration not found.', 'usp')], 500);
        }

        // Обновляем DTO данными из запроса, не пересобирая его
        $fields = $config->getFields();
        foreach (array_keys($fields) as $fieldName) {
            $postValue = $request->getPost($fieldName);
            if ($postValue !== null) {
                // Санация будет происходить внутри объектов полей при валидации
                $config->updateFieldValue($fieldName, wp_unslash($postValue));
            }
        }

        $form = $this->formFactory->create($config);

        if (!$form->validate()) {
            return $this->error(['message' => __('Validation error.', 'usp'), 'errors' => $form->getErrors()], 422);
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

        $settings = get_option('usp_settings', []);
        $requireConfirmation = !empty($settings['require_email_confirmation']);

        if ($requireConfirmation) {
            // Регистрация с подтверждением
            $userId = wp_insert_user($userData);
            if (is_wp_error($userId)) {
                return $this->error(['message' => $userId->get_error_message()], 409);
            }

            // Устанавливаем временную роль
            wp_update_user(['ID' => $userId, 'role' => 'need-confirm']);

            // Сохраняем мета-данные
            foreach ($metaData as $key => $value) {
                update_user_meta($userId, $key, $value);
            }

            $this->sendConfirmationEmail($userId, $userData);

            return $this->success(['message' => __('Registration successful! Please check your email to activate your account.', 'usp')]);

        } else {
            // Регистрация без подтверждения
            $userId = wp_create_user($userData['user_login'], $userData['user_pass'], $userData['user_email']);
            if (is_wp_error($userId)) {
                return $this->error(['message' => $userId->get_error_message()], 409);
            }

            // Сохраняем мета-данные
            foreach ($metaData as $key => $value) {
                update_user_meta($userId, $key, $value);
            }

            return $this->success(['message' => __('Registration successful!', 'usp')]);
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

        $user = get_user_by('login', $userLogin);

        if (!$user || md5($user->ID) !== $userIdHash || md5($this->securityHelper->getSecurityKey() . $user->ID) !== $securityHash) {
            wp_safe_redirect(home_url() . '?reg-error=invalid_token');
            exit;
        }

        // Активируем пользователя, устанавливая ему роль по умолчанию
        wp_update_user(['ID' => $user->ID, 'role' => get_option('default_role')]);

        // Перенаправляем на страницу входа с сообщением об успехе
        $settings = get_option('usp_settings', []);
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

        $subject = sprintf(__('[%s] Activate Your Account', 'usp'), get_bloginfo('name'));
        $message = sprintf(__("Thanks for signing up! To activate your account, please click this link:\n\n%s", 'usp'), $confirmationUrl);

        wp_mail($userData['user_email'], $subject, $message);
    }
}