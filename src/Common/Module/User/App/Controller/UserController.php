<?php

namespace UserSpace\Common\Module\User\App\Controller;

use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Common\Module\Form\Src\Domain\Service\FieldMapRegistryInterface;
use UserSpace\Common\Module\User\App\UseCase\Avatar\UpdateUserAvatarCommand;
use UserSpace\Common\Module\User\App\UseCase\Avatar\UpdateUserAvatarUseCase;
use UserSpace\Common\Module\User\App\UseCase\Login\LoginUserCommand;
use UserSpace\Common\Module\User\App\UseCase\Login\LoginUserUseCase;
use UserSpace\Common\Module\User\App\UseCase\Logout\LogoutUserUseCase;
use UserSpace\Common\Module\User\App\UseCase\PasswordReset\RequestPasswordResetCommand;
use UserSpace\Common\Module\User\App\UseCase\PasswordReset\RequestPasswordResetUseCase;
use UserSpace\Common\Module\User\App\UseCase\Registration\ConfirmRegistrationCommand;
use UserSpace\Common\Module\User\App\UseCase\Registration\ConfirmRegistrationUseCase;
use UserSpace\Common\Module\User\App\UseCase\Registration\RegisterUserCommand;
use UserSpace\Common\Module\User\App\UseCase\Registration\RegisterUserUseCase;
use UserSpace\Common\Module\User\App\UseCase\SseToken\GenerateSseTokenUseCase;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/user')]
class UserController extends AbstractController
{
    public const AVATAR_META_KEY = 'usp_avatar_id';

    public function __construct(
        private readonly UserApiInterface      $userApi,
        private readonly StringFilterInterface $str,
        private readonly SanitizerInterface    $sanitizer
    )
    {
    }

    /**
     * Обрабатывает загрузку и установку нового аватара для текущего пользователя.
     */
    #[Route(path: '/avatar', method: 'POST', permission: 'read')]
    public function updateAvatar(Request $request, UpdateUserAvatarUseCase $updateUserAvatarUseCase): JsonResponse
    {
        $clearedData = $this->sanitizer->sanitize($request->getPostParams(), [
            'attachmentId' => SanitizerRule::INT,
        ]);

        $command = new UpdateUserAvatarCommand(
            $clearedData->get('attachmentId', 0),
            $this->userApi->getCurrentUserId()
        );

        try {
            $updateUserAvatarUseCase->execute($command);

            return $this->success([
                'message' => $this->str->translate('Avatar updated successfully.'),
            ]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route(path: '/login', method: 'POST')]
    public function handleLogin(Request $request, LoginUserUseCase $loginUserUseCase): JsonResponse
    {
        $clearedData = $this->sanitizer->sanitize($request->getPostParams(), [
            'log' => SanitizerRule::TEXT_FIELD,
            'pwd' => SanitizerRule::NO_HTML, // Пароль не должен содержать HTML, но не должен изменяться
            'rememberme' => SanitizerRule::KEY,
            'redirect_to' => SanitizerRule::URL,
        ]);

        $command = new LoginUserCommand(
            username: $clearedData->get('log', ''),
            password: $request->getPost('pwd', ''), // Пароль передаем "сырым", так как он не должен быть изменен
            remember: $clearedData->get('rememberme') === 'forever',
            redirectTo: $clearedData->get('redirect_to')
        );

        try {
            $result = $loginUserUseCase->execute($command);

            return $this->success([
                'message' => $this->str->translate('Login successful!'),
                'redirect_url' => $result->redirectUrl,
            ]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route(path: '/logout', method: 'POST')]
    public function handleLogout(LogoutUserUseCase $logoutUserUseCase): JsonResponse
    {
        $logoutUserUseCase->execute();

        return $this->success(['message' => $this->str->translate('You have been logged out.')]);
    }

    #[Route(path: '/sse-token', method: 'POST')]
    public function generateSseToken(GenerateSseTokenUseCase $generateSseTokenUseCase): JsonResponse
    {
        try {
            $result = $generateSseTokenUseCase->execute($this->userApi->getCurrentUserId());

            return new JsonResponse([
                'token' => $result->token,
                'signature' => $result->signature,
                'expires_in' => $result->expiresIn,
            ]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route(path: '/password/reset', method: 'POST')]
    public function handlePasswordReset(Request $request, RequestPasswordResetUseCase $requestPasswordResetUseCase): JsonResponse
    {
        $clearedData = $this->sanitizer->sanitize($request->getPostParams(), [
            'user_login' => SanitizerRule::TEXT_FIELD,
        ]);

        $command = new RequestPasswordResetCommand($clearedData->get('user_login', ''));

        try {
            $requestPasswordResetUseCase->execute($command);
            return $this->success(['message' => $this->str->translate('Please check your email for the confirmation link.')]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route(path: '/register', method: 'POST')]
    public function handleRegistration(
        Request                    $request,
        RegisterUserUseCase        $registerUserUseCase,
        FormConfigManagerInterface $formConfigManager,
        FieldMapRegistryInterface  $fieldMapRegistry
    ): JsonResponse|UspException
    {

        /** @todo вынести тип формы в константу */
        $formType = 'registration';

        $formConfig = $formConfigManager->load($formType);

        /** @todo вынести процедуру создания конфига санитизации формы в сервис */
        $sanitizationConfig = array_map(function ($field) use ($fieldMapRegistry) {
            // получаем правила очистки поля по типу и добавляем в конфиг
            /** @var class-string<FieldInterface> $fieldClassName */
            $fieldClassName = $fieldMapRegistry->getClass($field['type']);
            return $fieldClassName::getSanitizationRule();
        }, $formConfig->getFields());

        $clearedData = $this->sanitizer->sanitize($request->getPostParams(), $sanitizationConfig);

        $command = new RegisterUserCommand($formType, $clearedData->all());

        try {
            $result = $registerUserUseCase->execute($command);
            return $this->success(['message' => $result->message]);
        } catch (UspException $e) {
            $errorData = ['message' => $e->getMessage()];
            if ($e->getCode() === 422) {
                $errorData['errors'] = $e->getData()['errors'] ?? [];
            }
            return $this->error($errorData, $e->getCode());
        }
    }

    #[Route(path: '/confirm-registration', method: 'GET')]
    public function confirmRegistration(Request $request, ConfirmRegistrationUseCase $confirmRegistrationUseCase): void
    {
        $clearedData = $this->sanitizer->sanitize($request->getGetParams(), [
            'token' => SanitizerRule::TEXT_FIELD,
        ]);

        $command = new ConfirmRegistrationCommand($clearedData->get('token', ''));

        try {
            $result = $confirmRegistrationUseCase->execute($command);
            wp_safe_redirect($result->redirectUrl);
        } catch (UspException $e) {
            wp_safe_redirect(home_url() . '?reg-error=invalid_token');
        }
        exit;
    }
}