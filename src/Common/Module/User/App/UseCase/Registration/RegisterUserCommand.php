<?php

namespace UserSpace\Common\Module\User\App\UseCase\Registration;

/**
 * Команда для выполнения регистрации пользователя.
 */
class RegisterUserCommand
{
    /**
     * @param string $formType
     * @param array<string, mixed> $registerData
     */
    public function __construct(
        public readonly string $formType,
        public readonly array  $registerData
    )
    {
    }
}