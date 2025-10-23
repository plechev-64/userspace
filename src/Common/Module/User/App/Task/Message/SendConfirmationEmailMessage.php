<?php

namespace UserSpace\Common\Module\User\App\Task\Message;

use UserSpace\Common\Module\Queue\Src\Domain\AbstractMessage;

if (!defined('ABSPATH')) {
    exit;
}

class SendConfirmationEmailMessage extends AbstractMessage
{
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
        public readonly string $body
    )
    {
    }
}