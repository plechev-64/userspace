<?php

namespace UserSpace\Common\Module\User\App\UseCase\Login;

/**
 * Команда для выполнения входа пользователя.
 */
class LoginUserCommand
{
    public function __construct(
        public readonly string  $username,
        public readonly string  $password,
        public readonly bool    $remember,
        public readonly ?string $redirectTo
    )
    {
    }
}