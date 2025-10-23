<?php

namespace UserSpace\Common\Module\User\App\UseCase\PasswordReset;

/**
 * Команда для запроса на сброс пароля.
 */
class RequestPasswordResetCommand
{
    public function __construct(
        public readonly string $userLogin
    )
    {
    }
}