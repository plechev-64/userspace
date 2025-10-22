<?php

namespace UserSpace\Core\Cron;

/**
 * Интерфейс для взаимодействия с WordPress Cron API.
 */
interface CronApiInterface
{
    /**
     * Проверяет, запланировано ли событие.
     * @param string $hook Имя хука.
     * @param array $args Аргументы.
     * @return int|false Временная метка следующего события или false.
     */
    public function nextScheduled(string $hook, array $args = []): int|false;

    /**
     * Планирует периодическое событие.
     * @param int $timestamp Временная метка первого запуска.
     * @param string $recurrence Название интервала повторения.
     * @param string $hook Имя хука.
     * @param array $args Аргументы.
     * @return bool
     */
    public function scheduleEvent(int $timestamp, string $recurrence, string $hook, array $args = []): bool;

    /**
     * Планирует одноразовое событие.
     * @param int $timestamp Временная метка запуска.
     * @param string $hook Имя хука.
     * @param array $args Аргументы.
     * @return bool
     */
    public function scheduleSingleEvent(int $timestamp, string $hook, array $args = []): bool;

    /**
     * Удаляет все запланированные события для указанного хука.
     * @param string $hook Имя хука.
     * @param array $args Аргументы.
     * @return int|false Количество удаленных событий или false.
     */
    public function clearScheduledHook(string $hook, array $args = []): int|false;
}