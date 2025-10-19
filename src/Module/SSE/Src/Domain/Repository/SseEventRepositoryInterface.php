<?php

namespace UserSpace\Module\SSE\Src\Domain\Repository;

if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для репозитория, управляющего SSE-событиями в базе данных.
 */
interface SseEventRepositoryInterface
{
    /**
     * Создает новое SSE-событие.
     *
     * @param string $eventType
     * @param array $payload
     * @return int|null
     */
    public function create(string $eventType, array $payload): ?int;

    /**
     * Находит новые события, начиная с указанного ID.
     *
     * @param int $lastEventId
     * @return array
     */
    public function findNewerThan(int $lastEventId): array;

    /**
     * Удаляет события до указанного ID включительно.
     *
     * @param int $lastEventId
     */
    public function deleteOlderThanOrEqual(int $lastEventId): void;

    /**
     * Удаляет старые SSE-события.
     *
     * @param string $beforeDate Дата в формате 'Y-m-d H:i:s', до которой нужно удалить записи.
     * @return int Количество удаленных строк.
     */
    public function pruneOldEvents(string $beforeDate): int;
}