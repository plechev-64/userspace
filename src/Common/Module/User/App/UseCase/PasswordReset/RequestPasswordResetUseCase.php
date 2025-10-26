<?php

namespace UserSpace\Common\Module\User\App\UseCase\PasswordReset;

use UserSpace\Core\Auth\AuthApiInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\WpApiInterface;

class RequestPasswordResetUseCase
{
    public function __construct(
        private readonly StringFilterInterface $str,
        private readonly AuthApiInterface      $authApi,
        private readonly WpApiInterface        $wpApi
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(RequestPasswordResetCommand $command): void
    {
        if (empty($command->userLogin)) {
            throw new UspException($this->str->translate('Please enter a username or email address.'), 400);
        }

        $result = $this->authApi->retrievePassword($command->userLogin);

        if ($this->wpApi->isWpError($result)) {
            // Предполагаем, что самая частая ошибка - "не найдено".
            // В будущем можно анализировать код ошибки для более точного HTTP-статуса.
            throw new UspException($result->get_error_message(), 404);
        }
    }
}