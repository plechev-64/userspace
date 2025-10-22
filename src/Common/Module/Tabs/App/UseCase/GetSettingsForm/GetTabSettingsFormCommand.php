<?php

namespace UserSpace\Common\Module\Tabs\App\UseCase\GetSettingsForm;

/**
 * Команда для получения формы настроек вкладки.
 */
class GetTabSettingsFormCommand
{
    public function __construct(
        public readonly string $tabConfigJson
    ) {
    }
}