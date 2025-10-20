<?php

namespace UserSpace\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для общих вспомогательных функций WordPress.
 */
interface WpApiInterface
{
    /**
     * Проверяет, является ли переменная объектом WP_Error.
     * Обертка для is_wp_error().
     */
    public function isWpError(mixed $thing): bool;

    /**
     * Определяет, выполняется ли AJAX-запрос.
     * Обертка для wp_doing_ajax().
     */
    public function isDoingAjax(): bool;

    /**
     * Определяет, выполняется ли CRON-запрос.
     * Обертка для wp_doing_cron().
     */
    public function isDoingCron(): bool;

    /**
     * Безопасное перенаправление на другой URL.
     * Обертка для wp_safe_redirect().
     *
     * @param string $location
     * @param int $status
     */
    public function safeRedirect(string $location, int $status = 302): void;
}