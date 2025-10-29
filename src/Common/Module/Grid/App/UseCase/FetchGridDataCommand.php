<?php

namespace UserSpace\Common\Module\Grid\App\UseCase;

/**
 * Команда для получения данных грида.
 */
class FetchGridDataCommand
{
    public function __construct(
        public readonly string $gridType,
        public readonly int    $page,
        public readonly int    $perPage,
        public readonly string $search,
        public readonly string $orderBy,
        public readonly string $order
    )
    {
    }
}