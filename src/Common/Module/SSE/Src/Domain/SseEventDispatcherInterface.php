<?php

namespace UserSpace\Common\Module\SSE\Src\Domain;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для управления отправкой событий для трансляции через Server-Sent Events.
 */
interface SseEventDispatcherInterface
{
    /**
     * Отправляет событие в "почтовый ящик" (БД) для последующей трансляции.
     *
     * @param string $eventType Тип события (например, 'job_processed').
     * @param array $payload Данные для передачи.
     * @return int|null
     */
    public function dispatchEvent(string $eventType, array $payload): ?int;

    /**
     * Отправляет событие конкретному пользователю.
     *
     * @param int    $userId    ID пользователя-получателя.
     * @param string $eventType Тип события.
     * @param array  $payload   Данные для передачи.
     * @return int|null ID созданного события или null в случае ошибки.
     */
    public function dispatchToUser(int $userId, string $eventType, array $payload): ?int;
}