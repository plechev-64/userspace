<?php

namespace UserSpace\Common\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

class LoginController extends AbstractController
{
    #[Route(path: '/login', method: 'POST')]
    public function handleLogin(Request $request): JsonResponse
    {
        $credentials = [
            'user_login'    => $request->getPost('log', ''),
            'user_password' => $request->getPost('pwd', ''),
            'remember'      => $request->getPost('rememberme') === 'forever',
        ];

        // Проверяем, что поля не пустые
        if (empty($credentials['user_login']) || empty($credentials['user_password'])) {
            return $this->error(['message' => __('Username and password are required.', 'usp')], 400);
        }

        $user = wp_signon($credentials, true);

        if (is_wp_error($user)) {
            return $this->error(['message' => $user->get_error_message()], 401);
        }

        // Определяем URL для перенаправления
        $redirect_to = $request->getPost('redirect_to');
        if (empty($redirect_to) || $redirect_to === admin_url()) {
            $redirect_to = home_url(); // По умолчанию на главную
        }

        // Фильтр для возможности изменить URL перенаправления
        $redirect_url = apply_filters('usp_login_redirect', $redirect_to, $user);

        return $this->success([
            'message' => __('Login successful. Redirecting...', 'usp'),
            'redirect_url' => $redirect_url,
        ]);
    }
}