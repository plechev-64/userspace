<?php

namespace UserSpace\Common\Module\SSE\Src\Domain\Repository;

if (!defined('ABSPATH')) {
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
     * @param string $eventType Тип события.
     * @param array $payload Данные события.
     * @param int|null $userId ID пользователя-получателя. NULL для глобального события.
     * @return int|null ID созданного события.
     */
    public function create(string $eventType, array $payload, ?int $userId = null): ?int;

    /**
     * Находит новые события, начиная с указанного ID.
     *
     * @param int $lastEventId ID последнего полученного события.
     * @param int|null $userId ID текущего пользователя для получения персональных и глобальных событий.
     * @return array Список новых событий.
     */
    public function findNewerThan(int $lastEventId, ?int $userId): array;

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