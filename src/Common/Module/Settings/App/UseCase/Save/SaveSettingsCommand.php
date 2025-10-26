<?php

namespace UserSpace\Common\Module\Settings\App\UseCase\Save;

/**
 * Команда для сохранения настроек плагина.
 */
class SaveSettingsCommand
{
    /**
     * @param array<string, string|array> $settingsPayload
     */
    public function __construct(
        public readonly array $settingsPayload
    )
    {
    }
}