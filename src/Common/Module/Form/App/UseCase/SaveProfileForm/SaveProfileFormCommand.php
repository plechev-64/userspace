<?php

namespace UserSpace\Common\Module\Form\App\UseCase\SaveProfileForm;

/**
 * Команда для сохранения данных формы профиля.
 */
class SaveProfileFormCommand
{
    public function __construct(
        public readonly string $formType,
        public readonly array $requestData
    ) {
    }
}