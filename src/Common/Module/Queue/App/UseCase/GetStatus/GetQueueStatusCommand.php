<?php

namespace UserSpace\Common\Module\Queue\App\UseCase\GetStatus;

class GetQueueStatusCommand
{
    public function __construct(
        public readonly int    $page,
        public readonly string $orderby,
        public readonly string $order,
        public readonly string $search
    )
    {
    }
}