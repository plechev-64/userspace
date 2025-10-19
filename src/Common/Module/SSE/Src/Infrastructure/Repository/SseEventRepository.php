<?php

namespace UserSpace\Common\Module\SSE\Src\Infrastructure\Repository;

use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;

if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Репозиторий для управления SSE-событиями в базе данных.
 */
class SseEventRepository implements SseEventRepositoryInterface
{
    private const TABLE_NAME = 'userspace_sse_events';

    private \wpdb $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Создает новое SSE-событие.
     *
     * @param string $eventType
     * @param array $payload
     * @return int|null
     */
    public function create(string $eventType, array $payload): ?int
    {
        $result = $this->wpdb->insert(
            $this->wpdb->prefix . self::TABLE_NAME,
            [
                'event_type' => $eventType,
                'payload'    => wp_json_encode($payload),
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]
        );

        return $result ? $this->wpdb->insert_id : null;
    }

    /**
     * Находит новые события, начиная с указанного ID.
     *
     * @param int $lastEventId
     * @return array
     */
    public function findNewerThan(int $lastEventId): array
    {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->wpdb->prefix}" . self::TABLE_NAME . " WHERE id > %d ORDER BY id ASC",
            $lastEventId
        ));
    }

    /**
     * Удаляет события до указанного ID включительно.
     *
     * @param int $lastEventId
     */
    public function deleteOlderThanOrEqual(int $lastEventId): void
    {
        $this->wpdb->query($this->wpdb->prepare(
            "DELETE FROM {$this->wpdb->prefix}" . self::TABLE_NAME . " WHERE id <= %d",
            $lastEventId
        ));
    }

    /**
     * Удаляет старые SSE-события.
     *
     * @param string $beforeDate Дата в формате 'Y-m-d H:i:s', до которой нужно удалить записи.
     * @return int Количество удаленных строк.
     */
    public function pruneOldEvents(string $beforeDate): int
    {
        $result = $this->wpdb->query($this->wpdb->prepare(
            "DELETE FROM {$this->wpdb->prefix}" . self::TABLE_NAME . " WHERE created_at < %s",
            $beforeDate
        ));

        return $result === false ? 0 : $result;
    }

    /**
     * Создает таблицу в БД.
     */
    public static function createTable(): void
    {
        global $wpdb;
        $table_name      = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type VARCHAR(255) NOT NULL,
            payload LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Удаляет таблицу при деактивации плагина.
     */
    public static function dropTable(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }
}