<?php

namespace UserSpace\Common\Module\User\App\UseCase\Registration;

/**
 * Команда для подтверждения регистрации пользователя.
 */
class ConfirmRegistrationCommand
{
    public function __construct(
        public readonly string $token
    )
    {
    }
}