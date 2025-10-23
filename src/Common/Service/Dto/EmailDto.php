<?php

namespace UserSpace\Common\Service\Dto;

/**
 * DTO для отправки email-сообщения.
 */
class EmailDto
{
    public function __construct(
        public readonly string|array $to,
        public readonly string       $subject,
        public readonly string       $content,
        public readonly string       $template = 'emails/email-wrapper',
        public readonly array        $headers = [],
        public readonly array        $attachments = []
    )
    {
    }
}