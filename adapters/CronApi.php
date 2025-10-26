<?php

namespace UserSpace\Adapters;

use UserSpace\Core\Cron\CronApiInterface;

class CronApi implements CronApiInterface
{
    public function nextScheduled(string $hook, array $args = []): int|false
    {
        return wp_next_scheduled($hook, $args);
    }

    public function scheduleEvent(int $timestamp, string $recurrence, string $hook, array $args = []): bool
    {
        return wp_schedule_event($timestamp, $recurrence, $hook, $args);
    }

    public function scheduleSingleEvent(int $timestamp, string $hook, array $args = []): bool
    {
        return wp_schedule_single_event($timestamp, $hook, $args);
    }

    public function clearScheduledHook(string $hook, array $args = []): int|false
    {
        return wp_clear_scheduled_hook($hook, $args);
    }
}