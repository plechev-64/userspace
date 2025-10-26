<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Service\Dto\EmailDto;

/**
 * Интерфейс для сервиса отправки email-сообщений.
 */
interface MailerServiceInterface
{
    /**
     * Отправляет email, используя DTO.
     */
    public function send(EmailDto $dto): void;
}