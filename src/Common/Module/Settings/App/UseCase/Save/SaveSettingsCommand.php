<?php

namespace UserSpace\Common\Module\Settings\App\UseCase\Save;

/**
 * Команда для сохранения настроек плагина.
 */
class SaveSettingsCommand
{
    public function __construct(
        public readonly array $settingsPayload
    ) {
    }
}