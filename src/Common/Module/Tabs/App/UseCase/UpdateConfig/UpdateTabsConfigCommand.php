<?php

namespace UserSpace\Common\Module\Tabs\App\UseCase\UpdateConfig;

/**
 * Команда для обновления конфигурации вкладок.
 */
class UpdateTabsConfigCommand
{
    public function __construct(
        public readonly string $configJson
    ) {
    }
}