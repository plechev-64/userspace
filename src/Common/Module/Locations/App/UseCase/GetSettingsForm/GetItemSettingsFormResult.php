<?php

namespace UserSpace\Common\Module\Locations\App\UseCase\GetSettingsForm;

use UserSpace\Common\Module\Form\Src\Domain\FormInterface;

/**
 * Результат успешного получения формы настроек вкладки.
 */
class GetItemSettingsFormResult
{
    public function __construct(
        public readonly FormInterface $form
    )
    {
    }
}