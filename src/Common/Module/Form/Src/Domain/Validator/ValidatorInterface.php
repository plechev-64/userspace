<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Validator;

use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для всех классов-валидаторов полей.
 */
interface ValidatorInterface
{
    /**
     * @param FieldInterface $field Поле для валидации.
     * @return string|null Сообщение об ошибке в случае неудачи или null в случае успеха.
     */
    public function validate(FieldInterface $field): ?string;
}