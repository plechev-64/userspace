<?php
/**
 * UserSpace Worker Endpoint
 *
 * Этот файл предназначен для обработки "легких" запросов, таких как Server-Sent Events (SSE)
 * или задач очереди, минуя полную загрузку ядра WordPress.
 * Это позволяет значительно снизить нагрузку на сервер и ускорить выполнение специфических задач.
 */

// Определяем минимально необходимые константы WordPress.
// Это предотвращает полную загрузку ядра WordPress и его компонентов.
define('DOING_AJAX', true); // Имитируем AJAX-запрос, чтобы некоторые плагины не вмешивались
define('WP_USE_THEMES', false); // Не загружаем тему
define('ABSPATH', dirname(__DIR__, 3) . '/'); // Путь к корневой директории WordPress

// Загружаем минимальный набор WordPress для доступа к функциям (например, wp_date, wp_mail, $wpdb)
// Но при этом избегаем полной инициализации плагинов и тем.
// Если вам не нужны никакие функции WordPress, кроме $wpdb, можно загрузить только wp-load.php
// и затем вручную инициализировать $wpdb.
require_once ABSPATH . 'wp-load.php';

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

// 1. Определение констант плагина (дублируем из userspace.php, так как он не загружается полностью)
const USERSPACE_VERSION = '1.0.0';
const USERSPACE_PLUGIN_FILE = __FILE__;
const USERSPACE_REST_NAMESPACE = 'userspace/v1';
define('USERSPACE_PLUGIN_DIR', plugin_dir_path(USERSPACE_PLUGIN_FILE));
define('USERSPACE_PLUGIN_URL', plugin_dir_url(USERSPACE_PLUGIN_FILE));
define('USERSPACE_WORKER_TOKEN', hash('sha256', NONCE_KEY . NONCE_SALT . 'userspace-worker'));

// 2. Подключение автозагрузчика Composer
if (file_exists(USERSPACE_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once USERSPACE_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Если автозагрузчик не найден, это критическая ошибка.
    // В продакшене можно просто выйти, в разработке - показать ошибку.
    exit('Composer autoloader not found. Please run `composer install`.');
}

use UserSpace\Plugin;
use UserSpace\Common\Module\SSE\App\SseController;

// Инициализируем контейнер и получаем SSE-контроллер
$container = Plugin::getInstance()->getContainer();
$sseController = $container->get(SseController::class);

// Обрабатываем запрос SSE
$sseController->streamEvents(UserSpace\Core\Http\Request::createFromGlobals());

// Завершаем выполнение скрипта
exit;