<?php

namespace UserSpace\Common\Module\Queue\App\UseCase\RunWorker;

class RunQueueWorkerCommand
{
    public function __construct(
        public readonly ?string $token
    )
    {
    }
}