<?php

namespace UserSpace\Common\Module\Form\Src\Domain;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для классов-валидаторов загружаемых файлов.
 */
interface FileValidatorInterface
{
    /**
     * @param array $file Файл из массива $_FILES для валидации.
     * @return string|null Сообщение об ошибке в случае неудачи или null в случае успеха.
     */
    public function validate(array $file): ?string;
}