<?php
/**
 * Plugin Name:       UserSpace
 * Plugin URI:        https://example.com/
 * Description:       Плагин для создания личного кабинета (личного пространства пользователя).
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      8.1
 * Author:            Your Name
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       userspace
 * Domain Path:       /languages
 */

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Определение констант плагина
define( 'USERSPACE_VERSION', '1.0.0' );
define( 'USERSPACE_PLUGIN_FILE', __FILE__ );
define( 'USERSPACE_PLUGIN_DIR', plugin_dir_path( USERSPACE_PLUGIN_FILE ) );
define( 'USERSPACE_PLUGIN_URL', plugin_dir_url( USERSPACE_PLUGIN_FILE ) );

// 2. Подключение автозагрузчика Composer
if ( file_exists( USERSPACE_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once USERSPACE_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Можно добавить уведомление в админ-панель о необходимости выполнить `composer install`
    // Например, через add_action( 'admin_notices', '...' );
}

// 3. Регистрация хуков активации и деактивации
// Обратите внимание, что мы используем полное имя класса с пространством имен
register_activation_hook( USERSPACE_PLUGIN_FILE, ['UserSpace\Plugin', 'activate' ] );
register_deactivation_hook( USERSPACE_PLUGIN_FILE, ['UserSpace\Plugin', 'deactivate' ] );

// 4. Запуск плагина
if ( class_exists('UserSpace\Plugin') ) {
    /**
     * Возвращает основной экземпляр плагина UserSpace.
     *
     * @return \UserSpace\Plugin
     */
    function userspace(): \UserSpace\Plugin {
        return \UserSpace\Plugin::getInstance();
    }

    // Запускаем плагин.
    userspace();
}