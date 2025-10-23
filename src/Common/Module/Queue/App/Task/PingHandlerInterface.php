<?php

namespace UserSpace\Common\Module\Queue\App\Task;

use UserSpace\Common\Module\Queue\Src\Domain\MessageHandlerInterface;
use UserSpace\Common\Module\Queue\Src\Domain\MessageInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class PingHandlerInterface implements MessageHandlerInterface
{

    /**
     * @param MessageInterface $message
     */
    public function handle(MessageInterface $message): void
    {
        // Имитируем полезную работу
        sleep(2);
    }
}