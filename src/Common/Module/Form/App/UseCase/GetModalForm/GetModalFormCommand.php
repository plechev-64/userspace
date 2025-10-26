<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetModalForm;

/**
 * Команда для получения HTML-кода модальной формы.
 */
class GetModalFormCommand
{
    public function __construct(
        public readonly string $formType
    )
    {
    }
}