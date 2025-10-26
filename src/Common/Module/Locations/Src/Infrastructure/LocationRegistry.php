<?php

namespace UserSpace\Common\Module\Locations\Src\Infrastructure;

use UserSpace\Common\Module\Locations\Src\Domain\LocationRegistryInterface;

/**
 * Сервис для регистрации мест вывода (локаций) для элементов меню личного кабинета.
 * Темы и дополнения используют этот сервис, чтобы объявить, какие локации они поддерживают.
 */
class LocationRegistry implements LocationRegistryInterface
{
    /**
     * @var array<string, string> Массив зарегистрированных локаций в формате ['name' => 'Label'].
     */
    private array $locations = [];

    /**
     * Конструктор. Регистрирует служебную локацию для неиспользуемых элементов.
     */
    public function __construct()
    {
        $this->locations[self::UNUSED_LOCATION] = 'Unassigned Items';
    }

    /**
     * Регистрирует новое место для вывода вкладок.
     *
     * @param string $name Уникальный идентификатор места (например, 'sidebar', 'header').
     * @param string $label Человекочитаемое название (например, 'Боковая панель', 'Шапка профиля').
     */
    public function registerLocation(string $name, string $label): void
    {
        if (!isset($this->locations[$name])) {
            $this->locations[$name] = $label;
        }
    }

    /**
     * Возвращает все зарегистрированные локации.
     *
     * @return array<string, string> Ассоциативный массив ['name' => 'Label'].
     */
    public function getRegisteredLocations(): array
    {
        return $this->locations;
    }

    /**
     * Проверяет, зарегистрирована ли указанная локация.
     *
     * @param string $name Идентификатор локации.
     * @return bool
     */
    public function isLocationRegistered(string $name): bool
    {
        return isset($this->locations[$name]);
    }
}