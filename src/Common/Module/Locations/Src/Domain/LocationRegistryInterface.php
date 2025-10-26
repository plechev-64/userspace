<?php

namespace UserSpace\Common\Module\Locations\Src\Domain;

/**
 * Интерфейс для сервиса регистрации мест вывода (локаций) для элементов меню личного кабинета.
 */
interface LocationRegistryInterface
{
    /**
     * Идентификатор для неиспользуемых элементов.
     */
    public const UNUSED_LOCATION = '_unused';

    /**
     * Регистрирует новое место для вывода элементов меню.
     *
     * @param string $name Уникальный идентификатор места (например, 'sidebar', 'header').
     * @param string $label Человекочитаемое название (например, 'Боковая панель', 'Шапка профиля').
     */
    public function registerLocation(string $name, string $label): void;

    /**
     * Возвращает все зарегистрированные локации.
     *
     * @return array<string, string> Ассоциативный массив ['name' => 'Label'].
     */
    public function getRegisteredLocations(): array;

    /**
     * Проверяет, зарегистрирована ли указанная локация.
     *
     * @param string $name Идентификатор локации.
     * @return bool
     */
    public function isLocationRegistered(string $name): bool;
}