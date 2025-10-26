<?php

namespace UserSpace\Common\Module\Grid\App\UseCase;

/**
 * Результат успешного получения данных грида.
 */
class FetchGridDataResult
{
    public function __construct(
        public readonly string $itemsHtml,
        public readonly string $paginationHtml,
        public readonly int    $totalItems,
        public readonly int    $totalPages,
        public readonly int    $currentPage
    )
    {
    }
}