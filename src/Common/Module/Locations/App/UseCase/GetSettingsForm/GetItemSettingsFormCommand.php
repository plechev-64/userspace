<?php

namespace UserSpace\Common\Module\Locations\App\UseCase\GetSettingsForm;

/**
 * Команда для получения формы настроек вкладки.
 */
class GetItemSettingsFormCommand
{
    public function __construct(
        public readonly string $tabConfigJson
    )
    {
    }
}