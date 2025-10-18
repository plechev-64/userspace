<?php

namespace UserSpace\Core\SSE;

if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Управляет отправкой событий для трансляции через Server-Sent Events.
 */
class SseManager
{
    private const TABLE_NAME = 'userspace_sse_events';

    /**
     * Отправляет событие в "почтовый ящик" (БД) для последующей трансляции.
     *
     * @param string $eventType Тип события (например, 'job_processed').
     * @param array $payload Данные для передачи.
     */
    public function dispatchEvent(string $eventType, array $payload): void
    {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . self::TABLE_NAME,
            [
                'event_type' => $eventType,
                'payload'    => wp_json_encode($payload),
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]
        );
    }
}