<?php

namespace UserSpace\Common\Module\Locations\App\UseCase\GetContent;

/**
 * Результат успешного получения контента вкладки.
 */
class GetTabContentResult
{
    public function __construct(
        public readonly string $html,
        public readonly array  $assets
    )
    {
    }
}