<?php

namespace UserSpace\Common\Module\User\App\UseCase\Registration;

/**
 * Результат успешного подтверждения регистрации.
 */
class ConfirmRegistrationResult
{
    public function __construct(
        public readonly string $redirectUrl
    )
    {
    }
}