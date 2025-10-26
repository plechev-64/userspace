<?php

namespace UserSpace\Common\Module\User\App\UseCase\Login;

/**
 * Результат успешного входа пользователя.
 */
class LoginUserResult
{
    public function __construct(
        public readonly string $redirectUrl
    )
    {
    }
}