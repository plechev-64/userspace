<?php

namespace UserSpace\Core\Cron;

/**
 * Интерфейс для управления Cron-задачами плагина.
 */
interface CronManagerInterface
{
    /**
     * Регистрирует все хуки и планирует Cron-задачи.
     */
    public function registerHooks(): void;

    /**
     * Удаляет все запланированные Cron-задачи плагина.
     */
    public function unregisterAllSchedules(): void;

    /**
     * Планирует немедленное одноразовое событие для продолжения обработки очереди.
     */
    public function scheduleImmediateBatch(): void;

    public function addDailyTask(string $hookName, callable $callback): void;
}