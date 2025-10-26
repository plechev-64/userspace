<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Form\Config;

/**
 * Интерфейс для сервиса, управляющего конфигурациями форм.
 */
interface FormConfigManagerInterface
{
    /**
     * Сохраняет конфигурацию формы в базу данных.
     *
     * @param string $type Тип формы (например, 'registration').
     * @param FormConfig $formConfig Конфигурационный DTO формы.
     *
     * @return int|false ID вставленной/обновленной записи или false в случае ошибки.
     */
    public function save(string $type, FormConfig $formConfig): int|false;

    /**
     * Загружает конфигурацию формы из базы данных.
     *
     * @param string $type Тип формы.
     *
     * @return FormConfig|null Конфигурационный DTO или null, если не найдено.
     */
    public function load(string $type): ?FormConfig;
}