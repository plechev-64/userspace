<?php

namespace UserSpace\Common\Module\User\App\UseCase\Login;

use UserSpace\Common\Module\Settings\App\SettingsEnum;
use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Core\Admin\AdminApiInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\SiteApiInterface;
use UserSpace\Core\String\StringFilterInterface;

class LoginUserUseCase
{
    private const SETTINGS_OPTION_NAME = 'usp_settings';

    public function __construct(
        private readonly StringFilterInterface  $str,
        private readonly UserApiInterface       $userApi,
        private readonly AdminApiInterface      $adminApi,
        private readonly SiteApiInterface       $siteApi,
        private readonly HookManagerInterface   $hookManager,
        private readonly OptionManagerInterface $optionManager
    )
    {
    }

    /**
     * @param LoginUserCommand $command
     * @return LoginUserResult Результат выполнения или адаптер ошибки.
     * @throws UspException
     */
    public function execute(LoginUserCommand $command): LoginUserResult
    {
        // 1. Валидация входных данных
        if (empty($command->username) || empty($command->password)) {
            throw new UspException($this->str->translate('Username and password are required.'), 400);
        }

        // 2. Попытка аутентификации
        $credentials = [
            'user_login' => $command->username,
            'user_password' => $command->password,
            'remember' => $command->remember,
        ];

        $user = $this->userApi->auth()->secureSignIn($credentials);

        if (UspException::isWpError($user)) {
            throw UspException::createFromWpError($user);
        }

        // 3. Определение URL для перенаправления
        $redirectTo = $this->siteApi->homeUrl(); // URL по умолчанию
        // Приоритет 1: URL из запроса (если он безопасен)
        if (!empty($command->redirectTo) && $command->redirectTo !== $this->adminApi->adminUrl()) {
            $redirectTo = $command->redirectTo;
        }

        // 4. Применение фильтра для возможности изменить URL
        $redirectUrl = $this->hookManager->applyFilters('usp_login_redirect', $redirectTo, $user);

        // 5. Возвращение результата
        return new LoginUserResult($redirectUrl);
    }
}