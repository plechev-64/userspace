<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm;

/**
 * Команда для получения HTML-кода формы настроек поля.
 */
class GetFieldSettingsFormCommand
{
    public function __construct(
        public readonly string $fieldType,
        public readonly string $fieldConfigJson
    )
    {
    }
}