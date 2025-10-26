<?php

namespace UserSpace\Common\Module\Form\App\UseCase\SaveConfig;

use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;

/**
 * Команда для сохранения конфигурации формы.
 */
class SaveFormConfigCommand
{
    /**
     * @param FormConfig $formConfig
     * @param string[] $deletedFields
     */
    public function __construct(
        public readonly FormConfig $formConfig,
        public readonly array      $deletedFields
    )
    {
    }
}