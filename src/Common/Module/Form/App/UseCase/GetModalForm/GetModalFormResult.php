<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetModalForm;

/**
 * Результат успешного получения HTML-кода модальной формы.
 */
class GetModalFormResult
{
    public function __construct(
        public readonly string $html
    )
    {
    }
}