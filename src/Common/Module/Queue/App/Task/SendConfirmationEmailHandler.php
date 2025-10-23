<?php

namespace UserSpace\Common\Module\Queue\App\Task;

use UserSpace\Common\Module\Queue\App\Task\Message\SendConfirmationEmailMessage;
use UserSpace\Common\Module\Queue\Src\Domain\MessageHandler;
use UserSpace\Core\WpApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

class SendConfirmationEmailHandler implements MessageHandler
{
    public function __construct(
        private readonly WpApiInterface $wpApi
    )
    {
    }

    public function handle(object $message): void
    {
        if (!$message instanceof SendConfirmationEmailMessage) {
            // В реальном приложении здесь можно логировать ошибку или выбрасывать исключение
            return;
        }

        $this->wpApi->mail($message->to, $message->subject, $message->body);
    }
}