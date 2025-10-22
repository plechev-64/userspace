<?php

namespace UserSpace\Common\Module\Queue\App\UseCase\RunWorker;

use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueManager;
use UserSpace\Core\Exception\UspException;

class RunQueueWorkerUseCase
{
    public function __construct(
        private readonly QueueManager $queueManager
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(RunQueueWorkerCommand $command): void
    {
        if (!$command->token || !hash_equals(USERSPACE_WORKER_TOKEN, $command->token)) {
            throw new UspException('Invalid worker token.', 403);
        }

        $this->queueManager->processQueueBatch();
    }
}