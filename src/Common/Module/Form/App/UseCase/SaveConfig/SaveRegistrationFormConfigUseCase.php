<?php

namespace UserSpace\Common\Module\Form\App\UseCase\SaveConfig;

use UserSpace\Common\Module\Form\Src\Infrastructure\Form\FormManager;

class SaveRegistrationFormConfigUseCase
{
    private const FORM_TYPE = 'registration';

    public function __construct(
        private readonly FormManager $formManager
    )
    {
    }

    public function execute(SaveFormConfigCommand $command): void
    {
        if (!empty($command->deletedFields)) {
            $this->processDeletedFields($command->deletedFields);
        }

        $this->formManager->save(self::FORM_TYPE, $command->formConfig);
    }

    /**
     * Для формы регистрации обычно не требуется никаких действий при удалении полей.
     */
    private function processDeletedFields(array $deletedFields): void
    {
        // No action needed for registration form.
    }
}