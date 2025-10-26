<?php

namespace UserSpace\Common\Module\Locations\App\UseCase\GetContent;

/**
 * Команда для получения контента вкладки.
 */
class GetTabContentCommand
{
    public function __construct(
        public readonly string $tabId
    )
    {
    }
}