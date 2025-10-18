<?php

namespace UserSpace\Core\Queue;

use UserSpace\Core\ContainerInterface;
use UserSpace\Core\SSE\SseManager;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Управляет асинхронной очередью задач.
 * Использует гибридный подход: WP-Cron запускает пакетный обработчик,
 * который работает в цикле с ограничением по времени.
 */
class QueueManager
{

    /**
     * Имя кастомной таблицы в БД для хранения задач.
     */
    private const TABLE_NAME = 'userspace_jobs';

    /**
     * Имя хука для WP-Cron, запускающего пакетную обработку.
     */
    private const CRON_HOOK_BATCH = 'userspace_process_queue_batch';

    /**
     * Имя хука для немедленного запуска воркера.
     */
    private const SPAWN_CRON_HOOK = 'userspace_spawn_queue_worker';

    /**
     * @var array<class-string<QueueableMessage>, class-string<MessageHandler>>
     */
    private array $messageHandlerMap;
    private ContainerInterface $container;
    private QueueStatus $status;
    private SseManager $sseManager;

    public function __construct(
        ContainerInterface $container,
        QueueStatus        $status,
        SseManager         $sseManager,
        array              $messageHandlerMap
    )
    {
        $this->container = $container;
        $this->status = $status;
        $this->sseManager = $sseManager;
        $this->messageHandlerMap = $messageHandlerMap;
    }

    /**
     * Запускает пакетную обработку очереди в цикле с ограничением по времени.
     */
    public function processQueueBatch(): void
    {
        $this->status->startRun();
        $jobsProcessed = 0;

        $time_limit = ini_get('max_execution_time');
        $safe_time_limit = $time_limit > 5 ? $time_limit - 5 : 25;
        $start_time = time();

        while (time() - $start_time < $safe_time_limit) {
            $jobId = $this->processSingleJob();
            // Если задача была обработана, увеличиваем счетчик.
            if ($jobId) {
                $jobsProcessed++;
                $this->sseManager->dispatchEvent('batch_processed', ['jobIdProcessed' => $jobId]);
            } else {
                // Если задач нет, делаем паузу в 1 секунду, чтобы не нагружать БД.
                sleep(1);
            }
        }

        $this->sseManager->dispatchEvent('batch_processed', ['JobsProcessed' => $jobsProcessed]);

        $this->status->endRun($jobsProcessed);

        if ($this->hasPendingJobs()) {
            if (!wp_next_scheduled(self::CRON_HOOK_BATCH)) {
                wp_schedule_single_event(time(), self::CRON_HOOK_BATCH);
            }
        }
    }

    /**
     * Находит и обрабатывает одну задачу из очереди.
     *
     * @return bool true, если задача была найдена и обработана, иначе false.
     */
    public function processSingleJob(): ?int
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $wpdb->query('START TRANSACTION');
        $job = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE status = %s AND available_at <= %s ORDER BY id ASC LIMIT 1 FOR UPDATE",
            'pending',
            gmdate('Y-m-d H:i:s')
        ));

        if (!$job) {
            $wpdb->query('COMMIT');
            return null;
        }

        $wpdb->update($table_name, ['status' => 'in_progress'], ['id' => $job->id]);
        $wpdb->query('COMMIT');

        $this->status->log(sprintf('Processing job #%d (%s)...', $job->id, $job->message_class));

        try {
            $messageClass = $job->message_class;

            if (!isset($this->messageHandlerMap[$messageClass])) {
                throw new \Exception("No handler registered for message '{$messageClass}'.");
            }

            $handlerClass = $this->messageHandlerMap[$messageClass];

            if (!$this->container->has($handlerClass)) {
                throw new \Exception("Handler class '{$handlerClass}' not found in container.");
            }

            /** @var MessageHandler $handlerInstance */
            $handlerInstance = $this->container->get($handlerClass);

            if (!class_exists($messageClass) || !is_subclass_of($messageClass, QueueableMessage::class)) {
                throw new \Exception("Message class '{$messageClass}' does not exist or does not implement QueueableMessage.");
            }

            $data = unserialize($job->args);
            $message = $messageClass::fromArray($data);

            $handlerInstance->handle($message);

            $wpdb->update($table_name, ['status' => 'completed'], ['id' => $job->id]);
            $this->status->log(sprintf('Job #%d completed successfully.', $job->id));

        } catch (\Throwable $e) {
            // В случае ошибки, пометить как 'failed'
            $wpdb->update(
                $table_name,
                [
                    'status' => 'failed',
                    'attempts' => $job->attempts + 1,
                ],
                ['id' => $job->id]
            );
            $this->status->log(sprintf('Job #%d failed. Error: %s', $job->id, $e->getMessage()));
        }

        return $job->id;
    }

    /**
     * Регистрирует хуки для WP-Cron.
     */
    public function registerHooks(): void
    {
        add_action(self::CRON_HOOK_BATCH, [$this, 'processQueueBatch']);

        // Добавляем обработчик для немедленного запуска
        add_action(self::SPAWN_CRON_HOOK, [$this, 'processQueueBatch']);

        if (!wp_next_scheduled(self::CRON_HOOK_BATCH)) {
            wp_schedule_event(time(), 'five_minutes', self::CRON_HOOK_BATCH);
        }

        add_filter('cron_schedules', function ($schedules) {
            if (!isset($schedules['five_minutes'])) {
                $schedules['five_minutes'] = [
                    'interval' => 300,
                    'display' => __('Every 5 Minutes', 'userspace'),
                ];
            }

            return $schedules;
        });
    }

    /**
     * Удаляет задачу из WP-Cron при деактивации.
     */
    public static function unregisterCronHooks(): void
    {
        wp_clear_scheduled_hook(self::CRON_HOOK_BATCH);
    }

    /**
     * Отправляет событие в таблицу для SSE.
     *
     * @param string $eventType
     * @param array $payload
     */
    private function dispatchSseEvent(string $eventType, array $payload): void
    {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'userspace_sse_events',
            [
                'event_type' => $eventType,
                'payload' => wp_json_encode($payload),
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Проверяет, есть ли в очереди ожидающие задачи.
     *
     * @return bool
     */
    private function hasPendingJobs(): bool
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE status = %s AND available_at <= %s",
            'pending',
            gmdate('Y-m-d H:i:s')
        ));

        return (int)$count > 0;
    }
}