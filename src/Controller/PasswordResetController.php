<?php

namespace UserSpace\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

class PasswordResetController extends AbstractController
{
    #[Route(path: '/password/reset', method: 'POST')]
    public function handlePasswordReset(Request $request): JsonResponse
    {
        $user_login = $request->getPost('user_login', '');

        if (empty($user_login)) {
            return $this->error(['message' => __('Please enter a username or email address.', 'usp')], 400);
        }

        // Используем стандартную, безопасную функцию WordPress для сброса пароля.
        // Она генерирует ключ, сохраняет его и отправляет письмо пользователю.
        $result = retrieve_password($user_login);

        if (is_wp_error($result)) {
            return $this->error(['message' => $result->get_error_message()], 404);
        }

        return $this->success(['message' => __('Please check your email for a link to reset your password.', 'usp')]);
    }
}