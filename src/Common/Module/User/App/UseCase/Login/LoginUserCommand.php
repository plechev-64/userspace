<?php

namespace UserSpace\Common\Module\User\App\UseCase\Login;

/**
 * Команда для выполнения входа пользователя.
 */
class LoginUserCommand
{
    public function __construct(
        public readonly array $loginData,
    )
    {
    }
}