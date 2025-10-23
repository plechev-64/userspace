<?php

namespace UserSpace\Common\Module\User\App\UseCase\Registration;

/**
 * Результат успешной регистрации пользователя.
 */
class RegisterUserResult
{
    public function __construct(
        public readonly string $message
    )
    {
    }
}