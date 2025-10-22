<?php

namespace UserSpace\Common\Module\Queue\App\UseCase\DispatchPing;

use UserSpace\Common\Module\Queue\App\Task\Message\PingMessage;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueDispatcher;

class DispatchPingTaskUseCase
{
    public function __construct(
        private readonly QueueDispatcher $dispatcher
    )
    {
    }

    public function execute(): void
    {
        $message = new PingMessage(time());
        $this->dispatcher->dispatch($message);
    }
}