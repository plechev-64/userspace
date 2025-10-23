<?php

namespace UserSpace\Common\Module\User\App\UseCase\Registration;

/**
 * Команда для выполнения регистрации пользователя.
 */
class RegisterUserCommand
{
    public function __construct(
        public readonly string $formType,
        public readonly array  $requestData
    )
    {
    }
}