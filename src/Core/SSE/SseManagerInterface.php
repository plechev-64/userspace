<?php

namespace UserSpace\Core\SSE;

if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для управления отправкой событий для трансляции через Server-Sent Events.
 */
interface SseManagerInterface
{
    /**
     * Отправляет событие в "почтовый ящик" (БД) для последующей трансляции.
     *
     * @param string $eventType Тип события (например, 'job_processed').
     * @param array $payload Данные для передачи.
     * @return int|null
     */
    public function dispatchEvent(string $eventType, array $payload): ?int;
}