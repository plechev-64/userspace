<?php

namespace UserSpace\Common\Module\Settings\Src\Domain;

use UserSpace\Common\Module\Settings\App\SettingsEnum;

/**
 * Интерфейс для сервиса, предоставляющего доступ к глобальным настройкам плагина.
 * Реализует паттерн "ленивой" загрузки и кэширования настроек на один запрос.
 */
interface PluginSettingsInterface
{
    /**
     * Возвращает значение конкретной настройки по ее ключу.
     *
     * @param string $key Ключ настройки (например, 'login_page_id').
     * @param mixed|null $default Значение по умолчанию, если настройка не найдена.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Обновляет значение конкретной настройки.
     */
    public function update(SettingsEnum|string $key, mixed $value): void;

    /**
     * Обновляет все настройки плагина.
     * @param array $value
     * @return void
     */
    public function updateAll(array $value): void;

    /**
     * Возвращает все настройки плагина в виде массива.
     * @return array
     */
    public function all(): array;
}