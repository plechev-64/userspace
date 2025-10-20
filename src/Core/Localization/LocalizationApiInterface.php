<?php

namespace UserSpace\Core\Localization;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для взаимодействия с функциями локализации WordPress.
 */
interface LocalizationApiInterface
{
    /**
     * Загружает файл перевода плагина.
     * Обертка для load_plugin_textdomain().
     */
    public function loadPluginTextdomain(string $domain, string $pluginRelPath): bool;
}