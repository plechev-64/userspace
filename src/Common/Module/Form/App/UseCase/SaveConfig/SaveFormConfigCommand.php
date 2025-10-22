<?php

namespace UserSpace\Common\Module\Form\App\UseCase\SaveConfig;

/**
 * Команда для сохранения конфигурации формы.
 */
class SaveFormConfigCommand
{
    public function __construct(
        public readonly string $configJson,
        public readonly string $deletedFieldsJson
    ) {
    }
}