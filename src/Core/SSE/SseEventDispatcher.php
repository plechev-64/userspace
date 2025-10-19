<?php

namespace UserSpace\Core\SSE;

use UserSpace\Core\SSE\Repository\SseEventRepositoryInterface;

if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Управляет отправкой событий для трансляции через Server-Sent Events.
 */
class SseEventDispatcher implements SseEventDispatcherInterface
{
    private SseEventRepositoryInterface $repository;

    public function __construct(SseEventRepositoryInterface $repository)
    {
        $this->repository = $repository;
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
}