<?php

namespace UserSpace\Common\Module\Queue\App\Task\Message;

use UserSpace\Common\Module\Queue\Src\Domain\AbstractMessage;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Сообщение для отправки приветственного письма.
 */
class SendWelcomeEmailMessage extends AbstractMessage
{
    public function __construct(
        public readonly int    $userId,
        public readonly string $templateName = 'default'
    )
    {
    }
}