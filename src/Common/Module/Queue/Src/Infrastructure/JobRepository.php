<?php

namespace UserSpace\Common\Module\Queue\Src\Infrastructure;

use UserSpace\Common\Module\Queue\Src\Domain\JobRepositoryInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Репозиторий для управления задачами (jobs) в базе данных.
 * Инкапсулирует всю логику SQL-запросов к таблице userspace_jobs.
 */
class JobRepository implements JobRepositoryInterface
{
    private const TABLE_NAME = 'userspace_jobs';

    private \wpdb $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Находит одну ожидающую задачу, блокирует ее для обработки и помечает как 'in_progress'.
     *
     * @return \stdClass|null Объект задачи или null, если задач нет.
     */
    public function findAndLockOnePendingJob(): ?\stdClass
    {
        $table_name = $this->wpdb->prefix . self::TABLE_NAME;

        $this->wpdb->query('START TRANSACTION');
        $job = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE status = %s AND available_at <= %s ORDER BY id ASC LIMIT 1 FOR UPDATE",
            'pending',
            gmdate('Y-m-d H:i:s')
        ));

        if (!$job) {
            $this->wpdb->query('COMMIT');

            return null;
        }

        $this->wpdb->update($table_name, ['status' => 'in_progress'], ['id' => $job->id]);
        $this->wpdb->query('COMMIT');

        return $job;
    }

    /**
     * Помечает задачу как успешно выполненную.
     *
     * @param int $jobId
     */
    public function markAsCompleted(int $jobId): void
    {
        $this->wpdb->update(
            $this->wpdb->prefix . self::TABLE_NAME,
            ['status' => 'completed'],
            ['id' => $jobId]
        );
    }

    /**
     * Помечает задачу как проваленную и увеличивает счетчик попыток.
     *
     * @param int $jobId
     * @param int $newAttemptCount
     */
    public function markAsFailed(int $jobId, int $newAttemptCount): void
    {
        $this->wpdb->update(
            $this->wpdb->prefix . self::TABLE_NAME,
            [
                'status' => 'failed',
                'attempts' => $newAttemptCount,
            ],
            ['id' => $jobId]
        );
    }

    /**
     * Проверяет, есть ли в очереди ожидающие задачи.
     *
     * @return bool
     */
    public function hasPendingJobs(): bool
    {
        $table_name = $this->wpdb->prefix . self::TABLE_NAME;

        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE status = %s AND available_at <= %s",
            'pending',
            gmdate('Y-m-d H:i:s')
        ));

        return (int)$count > 0;
    }

    /**
     * Создает новую задачу в очереди.
     *
     * @param string $messageClass
     * @param array $args
     * @param int $delaySeconds
     * @return int|null
     */
    public function create(string $messageClass, array $args, int $delaySeconds): ?int
    {
        $result = $this->wpdb->insert($this->wpdb->prefix . self::TABLE_NAME, [
                'message_class' => $messageClass,
                'args' => serialize($args),
                'available_at' => gmdate('Y-m-d H:i:s', time() + $delaySeconds),
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]
        );

        return $result ? $this->wpdb->insert_id : null;
    }

    /**
     * Удаляет старые выполненные задачи.
     *
     * @param string $beforeDate Дата в формате 'Y-m-d H:i:s', до которой нужно удалить записи.
     * @return int Количество удаленных строк.
     */
    public function pruneOldJobs(string $beforeDate): int
    {
        $result = $this->wpdb->query($this->wpdb->prepare(
            "DELETE FROM {$this->wpdb->prefix}" . self::TABLE_NAME . " WHERE status = %s AND created_at < %s",
            'completed',
            $beforeDate
        ));

        return $result === false ? 0 : $result;
    }

    /**
     * Создает таблицу в БД при активации плагина.
     */
    public static function createTable(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            message_class VARCHAR(255) NOT NULL,
            args LONGTEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            attempts INT(11) NOT NULL DEFAULT 0,
            available_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY status_available_at (status, available_at)
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