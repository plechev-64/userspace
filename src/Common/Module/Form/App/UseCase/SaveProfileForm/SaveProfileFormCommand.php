<?php

namespace UserSpace\Common\Module\Form\App\UseCase\SaveProfileForm;

/**
 * Команда для сохранения данных формы профиля.
 */
class SaveProfileFormCommand
{
    /**
     * @param string $formType
     * @param array<string, mixed> $fieldsWithValues Массив новых значений для полей профиля, где ключ - ИД поля, а значение - значение для этого поля.
     */
    public function __construct(
        public readonly string $formType,
        public readonly array  $fieldsWithValues
    )
    {
    }
}