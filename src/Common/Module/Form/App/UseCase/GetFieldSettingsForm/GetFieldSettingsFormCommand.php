<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;

/**
 * Команда для получения HTML-кода формы настроек поля.
 */
class GetFieldSettingsFormCommand
{
    public function __construct(
        public readonly AbstractFieldDto $fieldDto
    )
    {
    }
}