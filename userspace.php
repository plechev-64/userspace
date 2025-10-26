<?php
/**
 * Plugin Name:       UserSpace
 * Plugin URI:        https://example.com/
 * Description:       Плагин для создания личного кабинета (личного пространства пользователя).
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      8.1
 * Author:            Plechev Andrey
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       usp
 * Domain Path:       /languages
 */

// Защита от прямого доступа к файлу
use UserSpace\Common\Service\PluginLifecycle;
use UserSpace\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

// 1. Определение констант плагина
if (!defined('USERSPACE_VERSION')) define('USERSPACE_VERSION', '1.0.0');
if (!defined('USERSPACE_PLUGIN_FILE')) define('USERSPACE_PLUGIN_FILE', __FILE__);
if (!defined('USERSPACE_REST_NAMESPACE')) define('USERSPACE_REST_NAMESPACE', 'userspace/v1');
if (!defined('USERSPACE_PLUGIN_DIR')) define('USERSPACE_PLUGIN_DIR', plugin_dir_path(USERSPACE_PLUGIN_FILE));
if (!defined('USERSPACE_PLUGIN_URL')) define('USERSPACE_PLUGIN_URL', plugin_dir_url(USERSPACE_PLUGIN_FILE));
if (!defined('USERSPACE_WORKER_TOKEN')) define('USERSPACE_WORKER_TOKEN', hash('sha256', NONCE_KEY . NONCE_SALT . 'userspace-worker'));

// 2. Подключение автозагрузчика Composer
if (file_exists(USERSPACE_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once USERSPACE_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Можно добавить уведомление в админ-панель о необходимости выполнить `composer install`
    // Например, через add_action( 'admin_notices', '...' );
}

$container = Plugin::getInstance()->getContainer();
$lifecycle = $container->get(PluginLifecycle::class);
register_activation_hook(USERSPACE_PLUGIN_FILE, [$lifecycle, 'onActivation']);
register_deactivation_hook(USERSPACE_PLUGIN_FILE, [$lifecycle, 'onDeactivation']);
add_action('admin_init', [$lifecycle, 'redirectOnActivation']);

// 4. Запуск плагина
/**
 * Возвращает основной экземпляр плагина UserSpace.
 *
 * @return Plugin
 */
function userspace(): Plugin
{
    return Plugin::getInstance();
}

// Запускаем плагин.
userspace();