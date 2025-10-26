<?php

namespace UserSpace\Common\Module\Queue\App\UseCase\GetStatus;

class GetQueueStatusResult
{
    public function __construct(
        public readonly array  $statusWidget,
        public readonly string $itemsHtml,
        public readonly string $paginationHtml
    )
    {
    }
}