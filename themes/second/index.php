<?php
/**
 * Theme Name: Second
 */

use UserSpace\Core\Tabs\TabManager;
use UserSpace\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

// 1. Получаем необходимые сервисы из контейнера
/** @var TabManager $tabManager */
$container = Plugin::getInstance()->getContainer();
$tabManager = $container->get(TabManager::class);

// 2. Получаем данные для шаблона
$tabs = $tabManager->getTabs(get_current_user_id());

// 3. Подключаем стили и скрипты для этой конкретной темы
$theme_url = plugin_dir_url(__FILE__);

wp_enqueue_style(
    'usp-account-theme-second-style',
    $theme_url . 'style.css',
    [],
    USERSPACE_VERSION
);
wp_enqueue_script(
    'usp-account-theme-second-script',
    $theme_url . 'script.js',
    [],
    USERSPACE_VERSION,
    true
);

// 4. Подключаем сам файл шаблона (представление), передавая ему переменные
include __DIR__ . '/template.php';