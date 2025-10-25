<?php

namespace UserSpace\Common\Module\Locations\App\UseCase\UpdateConfig;

/**
 * Команда для обновления конфигурации вкладок.
 */
class UpdateLocationConfigCommand
{
    public function __construct(
        public readonly string $configJson
    )
    {
    }
}