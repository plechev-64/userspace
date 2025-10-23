<?php

namespace UserSpace\Common\Module\Settings\Src\Domain;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Описывает интерфейс для управления опциями WordPress.
 */
interface OptionManagerInterface
{
    /**
     * Возвращает сервис для работы с временными данными (transients).
     *
     * @return TransientApiInterface
     */
    public function transient(): TransientApiInterface;

    /**
     * Получает значение опции по ее имени.
     *
     * @param string $option Имя опции.
     * @param mixed $default Значение по умолчанию, если опция не найдена.
     * @return mixed Значение опции.
     */
    public function get(string $option, mixed $default = false): mixed;

    /**
     * Добавляет новую опцию, если она еще не существует.
     * Обертка для add_option().
     *
     * @return bool True, если опция была добавлена, иначе false.
     */
    public function add(string $option, mixed $value, bool $autoload = true): bool;

    /**
     * Обновляет значение существующей опции.
     *
     * @param string $option Имя опции.
     * @param mixed $value Новое значение.
     * @return bool True в случае успеха, false в противном случае.
     */
    public function update(string $option, mixed $value): bool;

    /**
     * Удаляет опцию по имени.
     *
     * @param string $option Имя опции для удаления.
     * @return bool True в случае успеха, false в противном случае.
     */
    public function delete(string $option): bool;

    /**
     * Регистрирует настройку для WordPress Settings API.
     *
     * @param string $option_group Группа настроек.
     * @param string $option_name Имя опции.
     * @param array $args Аргументы для регистрации.
     */
    public function register(string $option_group, string $option_name, array $args = []): void;
}