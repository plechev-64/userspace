<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm;

use UserSpace\Common\Module\Form\Src\Domain\FormInterface;

/**
 * Результат успешного получения HTML-кода формы настроек поля.
 */
class GetFieldSettingsFormResult
{
    public function __construct(
        public readonly FormInterface $form
    )
    {
    }
}