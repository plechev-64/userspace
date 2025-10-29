<?php

namespace UserSpace\Common\Module\Grid\Src\Domain\DTO;

class GridRequestParamsDto
{
    public function __construct(
        public readonly int    $page,
        public readonly int    $perPage,
        public readonly string $search,
        public readonly string $orderBy,
        public readonly string $order
    )
    {
    }
}