<?php

namespace UserSpace\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для общих функций сайта WordPress.
 */
interface SiteApiInterface
{
    /**
     * Получает URL главной страницы сайта.
     * Обертка для home_url().
     */
    public function homeUrl(string $path = ''): string;
}