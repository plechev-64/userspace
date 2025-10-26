<?php

namespace UserSpace\Common\Module\Queue\App\UseCase\TriggerWorker;

use UserSpace\Core\Process\BackgroundProcessManager;

class TriggerQueueWorkerUseCase
{
    public function __construct(
        private readonly BackgroundProcessManager $backgroundProcess
    )
    {
    }

    public function execute(): void
    {
        $this->backgroundProcess->dispatch('/queue/run-worker');
    }
}