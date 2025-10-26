<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileCleanupServiceInterface;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueManager;
use UserSpace\Core\Cron\CronApiInterface;
use UserSpace\Core\Cron\CronManagerInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Управляет регистрацией и планированием всех Cron-задач в плагине.
 */
class CronManager implements CronManagerInterface
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

    /**
     * Имя хука для ежедневной очистки временных файлов.
     */
    private const PRUNE_TEMP_FILES_HOOK = 'userspace_prune_temp_files';

    public function __construct(
        private readonly QueueManager                         $queueManager,
        private readonly TemporaryFileCleanupServiceInterface $tempFileCleanupService,
        private readonly CronApiInterface                     $cronApi,
        private readonly HookManagerInterface                 $hookManager,
        private readonly StringFilterInterface                $str
    )
    {
    }

    /**
     * Регистрирует все хуки и планирует Cron-задачи.
     */
    public function registerHooks(): void
    {
        // Регистрация обработчиков
        $this->hookManager->addAction(self::CRON_HOOK_BATCH, [$this->queueManager, 'processQueueBatch']);
        $this->hookManager->addAction(self::SPAWN_CRON_HOOK, [$this->queueManager, 'processQueueBatch']);
        $this->hookManager->addAction(self::PRUNE_CRON_HOOK, [$this->queueManager, 'pruneOldJobs']);
        $this->hookManager->addAction(self::PRUNE_SSE_CRON_HOOK, [$this->queueManager, 'pruneOldSseEvents']);
        $this->hookManager->addAction(self::PRUNE_TEMP_FILES_HOOK, [$this->tempFileCleanupService, 'cleanup']);

        // Регистрация кастомных интервалов
        $this->hookManager->addFilter('cron_schedules', function (array $schedules): array {
            if (!isset($schedules['five_minutes'])) {
                $schedules['five_minutes'] = [
                    'interval' => 300,
                    'display' => $this->str->translate('Every 5 Minutes'),
                ];
            }

            return $schedules;
        });

        // Планирование задач
        if (!$this->cronApi->nextScheduled(self::CRON_HOOK_BATCH)) {
            $this->cronApi->scheduleEvent(time(), 'five_minutes', self::CRON_HOOK_BATCH);
        }

        if (!$this->cronApi->nextScheduled(self::PRUNE_CRON_HOOK)) {
            $this->cronApi->scheduleEvent(time(), 'daily', self::PRUNE_CRON_HOOK);
        }

        if (!$this->cronApi->nextScheduled(self::PRUNE_SSE_CRON_HOOK)) {
            $this->cronApi->scheduleEvent(time(), 'daily', self::PRUNE_SSE_CRON_HOOK);
        }

        if (!$this->cronApi->nextScheduled(self::PRUNE_TEMP_FILES_HOOK)) {
            $this->cronApi->scheduleEvent(time(), 'daily', self::PRUNE_TEMP_FILES_HOOK);
        }
    }

    /**
     * Удаляет все запланированные Cron-задачи плагина.
     */
    public function unregisterAllSchedules(): void
    {
        $this->cronApi->clearScheduledHook(self::CRON_HOOK_BATCH);
        $this->cronApi->clearScheduledHook(self::PRUNE_CRON_HOOK);
        $this->cronApi->clearScheduledHook(self::PRUNE_SSE_CRON_HOOK);
        $this->cronApi->clearScheduledHook(self::PRUNE_TEMP_FILES_HOOK);
        // SPAWN_CRON_HOOK не нужно очищать, так как он создается через wp_schedule_single_event
    }

    /**
     * Планирует немедленное одноразовое событие для продолжения обработки очереди.
     */
    public function scheduleImmediateBatch(): void
    {
        if (!$this->cronApi->nextScheduled(self::SPAWN_CRON_HOOK)) {
            $this->cronApi->scheduleSingleEvent(time(), self::SPAWN_CRON_HOOK);
        }
    }

    public function addDailyTask(string $hookName, callable $callback): void
    {
        $this->hookManager->addAction($hookName, $callback);
        if (!$this->cronApi->nextScheduled($hookName)) {
            $this->cronApi->scheduleEvent(time(), 'daily', $hookName);
        }
    }
}