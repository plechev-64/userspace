<?php

namespace UserSpace\Core\Hooks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для взаимодействия с системой хуков WordPress (actions и filters).
 * Абстрагирует функции add_action, add_filter, do_action, apply_filters.
 */
interface HookManagerInterface
{
    /**
     * Добавляет функцию к хуку-действию.
     * Обертка для add_action().
     *
     * @param string $hookName Имя действия.
     * @param callable $callback Вызываемая функция.
     * @param int $priority Порядок выполнения.
     * @param int $acceptedArgs Количество принимаемых аргументов.
     */
    public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void;

    /**
     * Добавляет функцию к хуку-фильтру.
     * Обертка для add_filter().
     *
     * @param string $hookName Имя фильтра.
     * @param callable $callback Вызываемая функция.
     * @param int $priority Порядок выполнения.
     * @param int $acceptedArgs Количество принимаемых аргументов.
     */
    public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void;

    /**
     * Вызывает все функции, прикрепленные к хуку-действию.
     * Обертка для do_action().
     *
     * @param string $hookName Имя действия.
     * @param mixed ...$args Дополнительные аргументы для передачи в функции.
     */
    public function doAction(string $hookName, ...$args): void;

    /**
     * Вызывает все функции, прикрепленные к хуку-фильтру, и возвращает измененное значение.
     * Обертка для apply_filters().
     *
     * @param string $hookName Имя фильтра.
     * @param mixed $value Исходное значение для фильтрации.
     * @param mixed ...$args Дополнительные аргументы для передачи в функции.
     * @return mixed Отфильтрованное значение.
     */
    public function applyFilters(string $hookName, mixed $value, ...$args): mixed;

    /**
     * Проверяет, было ли действие (action) уже выполнено.
     * Обертка для did_action().
     *
     * @param string $hookName Имя действия.
     * @return int Количество раз, которое действие было выполнено.
     */
    public function didAction(string $hookName): int;
}