<?php

namespace UserSpace\Common\Module\Queue\App\Task;

use UserSpace\Common\Module\Queue\Src\Domain\MessageHandler;
use UserSpace\Common\Module\Queue\Src\Domain\QueueableMessage;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class PingHandler implements MessageHandler
{

    /**
     * @param QueueableMessage $message
     */
    public function handle(QueueableMessage $message): void
    {
        // Имитируем полезную работу
        sleep(2);
    }
}