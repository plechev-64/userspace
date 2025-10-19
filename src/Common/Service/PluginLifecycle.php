<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Module\Form\Src\Infrastructure\Repository\FormRepository;
use UserSpace\Common\Module\Queue\Src\Infrastructure\JobRepository;
use UserSpace\Common\Module\SSE\Src\Infrastructure\Repository\SseEventRepository;

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

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        FormRepository::createTable();
        JobRepository::createTable();
        SseEventRepository::createTable();

        // Устанавливаем опцию, которая может понадобиться в будущем.
        add_option('userspace_version', USERSPACE_VERSION);

        flush_rewrite_rules();
    }

    /**
     * Выполняется при деактивации плагина.
     */
    public function onDeactivation(): void
    {
        // Удаляем все cron-задачи, связанные с очередью
        CronManager::unregisterHooks();
        FormRepository::dropTable();
        JobRepository::dropTable();
        SseEventRepository::dropTable();
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