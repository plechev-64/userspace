<?php

namespace UserSpace\Common\Module\SSE\Src\Infrastructure;

use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Common\Module\SSE\Src\Domain\SseEventDispatcherInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Управляет отправкой событий для трансляции через Server-Sent Events.
 */
class SseEventDispatcher implements SseEventDispatcherInterface
{
    public function __construct(
        private readonly SseEventRepositoryInterface $repository,
    )
    {
    }

    /**
     * Отправляет событие в "почтовый ящик" (БД) для последующей трансляции.
     *
     * @param string $eventType Тип события (например, 'job_processed').
     * @param array $payload Данные для передачи.
     */
    public function dispatchEvent(string $eventType, array $payload): ?int
    {
        return $this->repository->create($eventType, $payload);
    }

    /**
     * Отправляет событие конкретному пользователю.
     *
     * @inheritDoc
     */
    public function dispatchToUser(int $userId, string $eventType, array $payload): ?int
    {
        return $this->repository->create($eventType, $payload, $userId);

    }
}