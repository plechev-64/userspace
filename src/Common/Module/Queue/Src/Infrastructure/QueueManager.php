<?php

namespace UserSpace\Common\Module\Queue\Src\Infrastructure;

use UserSpace\Common\Module\Queue\Src\Domain\JobRepositoryInterface;
use UserSpace\Common\Module\Queue\Src\Domain\MessageHandler;
use UserSpace\Common\Module\Queue\Src\Domain\QueueableMessage;
use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Common\Module\SSE\Src\Domain\SseEventDispatcherInterface;
use UserSpace\Common\Service\CronManager;
use UserSpace\Core\Container\ContainerInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Управляет асинхронной очередью задач.
 * Использует гибридный подход: WP-Cron запускает пакетный обработчик,
 * который работает в цикле с ограничением по времени.
 */
final class QueueManager
{
    private const MAX_ATTEMPTS = 3;

    private ?CronManager $cronManager = null;

    public function __construct(
        private readonly ContainerInterface          $container,
        private readonly QueueStatus                 $status,
        private readonly SseEventDispatcherInterface $sseManager,
        private readonly JobRepositoryInterface      $jobRepository,
        private readonly SseEventRepositoryInterface $sseEventRepository,
        private readonly array                       $messageHandlerMap
    )
    {
    }

    /**
     * Устанавливает зависимость от CronManager (Setter Injection для избежания циклической зависимости).
     */
    public function setCronManager(CronManager $cronManager): void
    {
        $this->cronManager = $cronManager;
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
                $this->sseManager->dispatchEvent('batch_processed', ['IdProcessed' => $jobId, 'JobsProcessed' => $jobsProcessed]);
            } else {
                // Если задач нет, делаем паузу в 1 секунду, чтобы не нагружать БД.
                sleep(1);
            }
        }

        $this->status->endRun($jobsProcessed);

        // Если были обработаны задачи и в очереди еще есть что-то, планируем немедленный перезапуск.
        if ($this->cronManager && $jobsProcessed > 0 && $this->jobRepository->hasPendingJobs()) {
            $this->cronManager->scheduleImmediateBatch();
        }
    }

    /**
     * Находит и обрабатывает одну задачу из очереди.
     *
     * @return int|null ID обработанной задачи или null, если задач не было.
     */
    public function processSingleJob(): ?int
    {
        $job = $this->jobRepository->findAndLockOnePendingJob();

        if (!$job) {
            return null;
        }

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

            // Безопасная десериализация. Мы ожидаем только массив.
            $data = unserialize($job->args, ['allowed_classes' => false]);

            $message = $messageClass::fromArray($data);

            $handlerInstance->handle($message);

            $this->jobRepository->markAsCompleted($job->id);
            $this->status->log(sprintf('Job #%d completed successfully.', $job->id));

        } catch (\Throwable $e) {
            $newAttempts = $job->attempts + 1;
            $this->status->log(sprintf('Job #%d failed (attempt %d/%d). Error: %s', $job->id, $newAttempts, self::MAX_ATTEMPTS, $e->getMessage()));

            if ($newAttempts >= self::MAX_ATTEMPTS) {
                // Превышен лимит попыток, помечаем как окончательно проваленную
                $this->jobRepository->markAsFailed($job->id, $newAttempts);
                $this->status->log(sprintf('Job #%d has been marked as permanently failed.', $job->id));
            } else {
                // Планируем повторную попытку с экспоненциальной задержкой
                $delay = (int)(60 * pow(5, $newAttempts - 1)); // 1, 5, 25 минут
                $this->jobRepository->rescheduleAsFailed($job->id, $newAttempts, $delay);
                $this->status->log(sprintf('Job #%d rescheduled to run in %d seconds.', $job->id, $delay));
            }
        }

        return $job->id;
    }

    /**
     * Выполняет очистку старых выполненных задач.
     */
    public function pruneOldJobs(): void
    {
        // Удаляем задачи старше 10 дней
        $cutoffDate = (new \DateTime('-10 days'))->format('Y-m-d H:i:s');
        $deletedRows = $this->jobRepository->pruneOldJobs($cutoffDate);

        if ($deletedRows > 0) {
            $this->status->log(sprintf('Pruned %d old completed jobs from the queue.', $deletedRows));
        }
    }

    /**
     * Выполняет очистку старых SSE-событий.
     */
    public function pruneOldSseEvents(): void
    {
        // Удаляем события старше 1 дня
        $cutoffDate = (new \DateTime('-1 day'))->format('Y-m-d H:i:s');
        $deletedRows = $this->sseEventRepository->pruneOldEvents($cutoffDate);

        if ($deletedRows > 0) {
            $this->status->log(sprintf('Pruned %d old SSE events.', $deletedRows));
        }
    }
}