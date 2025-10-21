<?php

namespace UserSpace\Common\Module\Tabs\Src\Infrastructure;

/**
 * Сервис для регистрации мест вывода (локаций) для вкладок личного кабинета.
 * Темы используют этот сервис, чтобы объявить, какие локации они поддерживают.
 */
class TabLocationManager
{
    /**
     * Идентификатор для неиспользуемых вкладок.
     */
    public const UNUSED_LOCATION = '_unused';

    /**
     * @var array<string, string> Массив зарегистрированных локаций в формате ['name' => 'Label'].
     */
    private array $locations = [];

    /**
     * Конструктор. Регистрирует сервисную локацию по умолчанию.
     */
    public function __construct()
    {
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
        $this->locations[self::UNUSED_LOCATION] = 'Unassigned Tabs';
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