<?php

namespace UserSpace\Common\Module\Grid\App\UseCase;

/**
 * Команда для получения данных грида.
 */
class FetchGridDataCommand
{
    public function __construct(
        public readonly string $gridType,
        public readonly array  $requestParams
    )
    {
    }
}