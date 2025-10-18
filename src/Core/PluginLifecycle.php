<?php

namespace UserSpace\Core;

/**
 * Управляет жизненным циклом плагина (активация, деактивация, удаление).
 */
class PluginLifecycle
{
    private const REDIRECT_TRANSIENT = 'usp_setup_wizard_redirect';

    /**
     * Выполняется при активации плагина.
     *
     * - Устанавливает временный флаг для перенаправления на мастер настройки.
     * - Здесь можно добавить другие действия при активации (например, создание таблиц, сброс правил).
     */
    public function onActivation(): void
    {
        // Устанавливаем флаг для редиректа на 30 секунд.
        set_transient(self::REDIRECT_TRANSIENT, true, 30);

        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name = $wpdb->prefix . 'userspace_forms';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			type VARCHAR(100) NOT NULL,
			config LONGTEXT NOT NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY type (type)
		) {$charset_collate};";
        dbDelta($sql);

        $table_name      = $wpdb->prefix . 'userspace_jobs';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
             id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
             message_class VARCHAR(255) NOT NULL,
             args LONGTEXT NOT NULL,
             status VARCHAR(20) NOT NULL DEFAULT 'pending',
             attempts INT(11) NOT NULL DEFAULT 0,
             available_at DATETIME NOT NULL,
             created_at DATETIME NOT NULL,
             PRIMARY KEY  (id),
             KEY status_available_at (status, available_at)
         ) $charset_collate;";
        dbDelta( $sql );

        // Устанавливаем опцию, которая может понадобиться в будущем.
        add_option('userspace_version', USERSPACE_VERSION);

        flush_rewrite_rules();
    }

    /**
     * Выполняется при деактивации плагина.
     */
    public function onDeactivation(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'userspace_jobs';
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

        flush_rewrite_rules();
    }

    /**
     * Проверяет флаг и, если он установлен, перенаправляет пользователя
     * на страницу мастера первоначальной настройки.
     *
     * Вызывается на хуке `admin_init`.
     */
    public function redirectOnActivation(): void
    {
        if (get_transient(self::REDIRECT_TRANSIENT)) {
            delete_transient(self::REDIRECT_TRANSIENT);

            // Не перенаправляем при AJAX запросах, CRON, или сетевой активации.
            if (wp_doing_ajax() || wp_doing_cron() || isset($_GET['activate-multi'])) {
                return;
            }

            // Безопасное перенаправление.
            wp_safe_redirect(admin_url('admin.php?page=userspace-setup'));
            exit;
        }
    }
}