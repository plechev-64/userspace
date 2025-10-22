<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm;

/**
 * Результат успешного получения HTML-кода формы настроек поля.
 */
class GetFieldSettingsFormResult
{
    public function __construct(
        public readonly string $html
    ) {
    }
}