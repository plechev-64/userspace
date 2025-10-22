<?php

namespace UserSpace\Common\Module\Form\App\UseCase\SaveConfig;

use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class SaveRegistrationFormConfigUseCase
{
    private const FORM_TYPE = 'registration';

    public function __construct(
        private readonly FormManager           $formManager,
        private readonly StringFilterInterface $str
    ) {
    }

    /**
     * @throws UspException
     */
    public function execute(SaveFormConfigCommand $command): void
    {
        $configArray = json_decode($command->configJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UspException($this->str->translate('Invalid JSON format.'), 400);
        }

        $deletedFields = json_decode($command->deletedFieldsJson, true);
        if (is_array($deletedFields) && !empty($deletedFields)) {
            $this->processDeletedFields($deletedFields);
        }

        $formConfig = FormConfig::fromArray($configArray);
        $this->formManager->save(self::FORM_TYPE, $formConfig);
    }

    /**
     * Для формы регистрации обычно не требуется никаких действий при удалении полей.
     */
    private function processDeletedFields(array $deletedFields): void
    {
        // No action needed for registration form.
    }
}