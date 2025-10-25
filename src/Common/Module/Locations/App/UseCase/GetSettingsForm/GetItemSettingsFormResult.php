<?php

namespace UserSpace\Common\Module\Locations\App\UseCase\GetSettingsForm;

/**
 * Результат успешного получения формы настроек вкладки.
 */
class GetItemSettingsFormResult
{
    public function __construct(
        public readonly string $html
    )
    {
    }
}