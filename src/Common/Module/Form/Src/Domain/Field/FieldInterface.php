<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Field;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для всех полей формы.
 */
interface FieldInterface
{
    /**
     * Генерирует и возвращает HTML-представление поля.
     *
     * @return string
     */
    public function render(): string;

    /**
     * Генерирует HTML-код только для элемента ввода (input, select, etc.).
     *
     * @return string
     */
    public function renderInput(): string;

    /**
     * Проверяет значение поля на соответствие правилам.
     *
     * @return bool True, если значение валидно, иначе false.
     */
    public function validate(): bool;

    /**
     * Возвращает true, если поле прошло валидацию.
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Возвращает массив с сообщениями об ошибках валидации.
     *
     * @return string[]
     */
    public function getErrors(): array;

    /**
     * Возвращает текущее значение поля.
     *
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Возвращает имя (атрибут name) поля.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает конфигурацию формы для редактирования настроек этого поля.
     *
     * @return array
     */
    public function getSettingsFormConfig(): array;

    /**
     * Генерирует и возвращает HTML-представление значения поля для отображения пользователю.
     * Например, для поля 'select' это будет метка опции, а не ее ключ.
     *
     * @return string
     */
    public function renderValue(): string;

    public function setValue(mixed $value);

}