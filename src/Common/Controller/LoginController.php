<?php

namespace UserSpace\Common\Controller;

use UserSpace\Core\Admin\AdminApiInterface;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\SiteApiInterface;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\User\UserApiInterface;
use UserSpace\Core\WpApiInterface;

#[Route(path: '/login')]
class LoginController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface  $str,
        private readonly UserApiInterface       $userApi,
        private readonly WpApiInterface         $wpApi,
        private readonly AdminApiInterface      $adminApi,
        private readonly SiteApiInterface       $siteApi,
        private readonly HookManagerInterface   $hookManager
    )
    {
    }
    
    #[Route(path: '/login', method: 'POST')]
    public function handleLogin(Request $request): JsonResponse
    {
        $credentials = [
            'user_login' => $request->getPost('log', ''),
            'user_password' => $request->getPost('pwd', ''),
            'remember' => $request->getPost('rememberme') === 'forever',
        ];

        // Проверяем, что поля не пустые
        if (empty($credentials['user_login']) || empty($credentials['user_password'])) {
            return $this->error(['message' => $this->str->translate('Username and password are required.', 'usp')], 400);
        }

        $user = $this->userApi->auth()->secureSignIn($credentials);

        if ($this->wpApi->isWpError($user)) {
            return $this->error(['message' => $user->get_error_message()], 401);
        }

        // Определяем URL для перенаправления
        $redirect_to = $request->getPost('redirect_to');
        if (empty($redirect_to) || $redirect_to === $this->adminApi->adminUrl()) {
            $redirect_to = $this->siteApi->homeUrl(); // По умолчанию на главную
        }

        // Фильтр для возможности изменить URL перенаправления
        $redirect_url = $this->hookManager->applyFilters('usp_login_redirect', $redirect_to, $user);

        return $this->success([
            'message' => $this->str->translate('Login successful!'),
            'redirect_url' => $redirect_url,
        ]);
    }

    #[Route(path: '/logout', method: 'POST')]
    public function handleLogout(): JsonResponse
    {
        $this->userApi->auth()->logOut();

        return $this->success(['message' => $this->str->translate('You have been logged out.')]);
    }
}