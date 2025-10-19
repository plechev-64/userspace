<?php

namespace UserSpace\Common\Module\Queue\Src\Domain;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для репозитория, управляющего задачами (jobs) в базе данных.
 */
interface JobRepositoryInterface
{
    /**
     * Находит одну ожидающую задачу, блокирует ее для обработки и помечает как 'in_progress'.
     *
     * @return \stdClass|null Объект задачи или null, если задач нет.
     */
    public function findAndLockOnePendingJob(): ?\stdClass;

    /**
     * Помечает задачу как успешно выполненную.
     */
    public function markAsCompleted(int $jobId): void;

    /**
     * Помечает задачу как проваленную и увеличивает счетчик попыток.
     */
    public function markAsFailed(int $jobId, int $newAttemptCount): void;

    /**
     * Проверяет, есть ли в очереди ожидающие задачи.
     */
    public function hasPendingJobs(): bool;

    /**
     * Создает новую задачу в очереди.
     */
    public function create(string $messageClass, array $args, int $delaySeconds): ?int;

    /**
     * Удаляет старые выполненные задачи.
     */
    public function pruneOldJobs(string $beforeDate): int;
}