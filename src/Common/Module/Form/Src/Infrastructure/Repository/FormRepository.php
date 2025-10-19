<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Repository;

use UserSpace\Common\Module\Form\Src\Domain\Repository\FormRepositoryInterface;

if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Репозиторий для управления формами в базе данных.
 */
class FormRepository implements FormRepositoryInterface
{
    private const TABLE_NAME = 'userspace_forms';

    private \wpdb $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Находит форму по ее типу.
     *
     * @param string $type
     * @return object|null
     */
    public function findByType(string $type): ?object
    {
        $table_name = $this->wpdb->prefix . self::TABLE_NAME;

        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE type = %s",
                $type
            )
        );
    }

    /**
     * Создает или обновляет форму.
     *
     * @param string $type
     * @param array $config
     * @return int|false
     */
    public function createOrUpdate(string $type, array $config): int|false
    {
        $table_name = $this->wpdb->prefix . self::TABLE_NAME;
        $existing = $this->findByType($type);

        $data = [
            'type'   => $type,
            'config' => wp_json_encode($config),
        ];

        if ($existing) {
            return $this->wpdb->update($table_name, $data, ['id' => $existing->id]);
        }

        $data['created_at'] = current_time('mysql', 1);
        return $this->wpdb->insert($table_name, $data);
    }

    /**
     * Создает таблицу в БД.
     */
    public static function createTable(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			type VARCHAR(100) NOT NULL,
			config LONGTEXT NOT NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY type (type)
		) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Удаляет таблицу.
     */
    public static function dropTable(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }
}