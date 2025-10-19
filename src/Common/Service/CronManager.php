<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Управляет регистрацией и планированием всех Cron-задач в плагине.
 */
class CronManager
{
    /**
     * Имя хука для WP-Cron, запускающего пакетную обработку.
     */
    private const CRON_HOOK_BATCH = 'userspace_process_queue_batch';

    /**
     * Имя хука для немедленного запуска воркера.
     */
    private const SPAWN_CRON_HOOK = 'userspace_spawn_queue_worker';

    /**
     * Имя хука для ежедневной очистки старых задач.
     */
    private const PRUNE_CRON_HOOK = 'userspace_prune_old_jobs';

    /**
     * Имя хука для ежедневной очистки старых SSE-событий.
     */
    private const PRUNE_SSE_CRON_HOOK = 'userspace_prune_old_sse_events';

    public function __construct(private readonly QueueManager $queueManager)
    {
    }

    /**
     * Регистрирует все хуки и планирует Cron-задачи.
     */
    public function registerHooks(): void
    {
        // Регистрация обработчиков
        add_action(self::CRON_HOOK_BATCH, [$this->queueManager, 'processQueueBatch']);
        add_action(self::SPAWN_CRON_HOOK, [$this->queueManager, 'processQueueBatch']);
        add_action(self::PRUNE_CRON_HOOK, [$this->queueManager, 'pruneOldJobs']);
        add_action(self::PRUNE_SSE_CRON_HOOK, [$this->queueManager, 'pruneOldSseEvents']);

        // Регистрация кастомных интервалов
        add_filter('cron_schedules', function ($schedules) {
            if (!isset($schedules['five_minutes'])) {
                $schedules['five_minutes'] = [
                    'interval' => 300,
                    'display' => __('Every 5 Minutes', 'userspace'),
                ];
            }

            return $schedules;
        });

        // Планирование задач
        if (!wp_next_scheduled(self::CRON_HOOK_BATCH)) {
            wp_schedule_event(time(), 'five_minutes', self::CRON_HOOK_BATCH);
        }

        if (!wp_next_scheduled(self::PRUNE_CRON_HOOK)) {
            wp_schedule_event(time(), 'daily', self::PRUNE_CRON_HOOK);
        }

        if (!wp_next_scheduled(self::PRUNE_SSE_CRON_HOOK)) {
            wp_schedule_event(time(), 'daily', self::PRUNE_SSE_CRON_HOOK);
        }
    }

    /**
     * Удаляет все запланированные Cron-задачи плагина.
     */
    public static function unregisterHooks(): void
    {
        wp_clear_scheduled_hook(self::CRON_HOOK_BATCH);
        wp_clear_scheduled_hook(self::PRUNE_CRON_HOOK);
        wp_clear_scheduled_hook(self::PRUNE_SSE_CRON_HOOK);
        // SPAWN_CRON_HOOK не нужно очищать, так как он создается через wp_schedule_single_event
    }

    /**
     * Планирует немедленное одноразовое событие для продолжения обработки очереди.
     */
    public function scheduleImmediateBatch(): void
    {
        if (!wp_next_scheduled(self::SPAWN_CRON_HOOK)) {
            wp_schedule_single_event(time(), self::SPAWN_CRON_HOOK);
        }
    }
}