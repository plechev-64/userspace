<?php

namespace UserSpace\Common\Module\Tabs\App\UseCase\GetContent;

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