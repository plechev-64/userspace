<?php

namespace UserSpace\Common\Module\Queue\Src\Infrastructure;

use UserSpace\Common\Module\Queue\Src\Domain\JobRepositoryInterface;
use UserSpace\Core\Database\DatabaseConnectionInterface;

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

    private readonly DatabaseConnectionInterface $db;

    public function __construct(DatabaseConnectionInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Находит одну ожидающую задачу, блокирует ее для обработки и помечает как 'in_progress'.
     *
     * @return \stdClass|null Объект задачи или null, если задач нет.
     */
    public function findAndLockOnePendingJob(): ?\stdClass
    {
        $transaction = $this->db->transaction();
        $transaction->beginTransaction();
        $builder = $this->db->queryBuilder();

        $query = "SELECT * FROM {$this->db->getTableName(self::TABLE_NAME)} WHERE status = %s AND available_at <= %s ORDER BY id ASC LIMIT 1 FOR UPDATE";
        $job = $builder->firstRaw($query, 'pending', gmdate('Y-m-d H:i:s'));

        if (!$job) {
            $transaction->commit();
            return null;
        }

        $builder->from(self::TABLE_NAME)->where('id', '=', $job->id)->update(['status' => 'in_progress']);
        $transaction->commit();

        return $job;
    }

    /**
     * Помечает задачу как успешно выполненную.
     *
     * @param int $jobId
     */
    public function markAsCompleted(int $jobId): void
    {
        $this->db->queryBuilder()
            ->from(self::TABLE_NAME)
            ->where('id', '=', $jobId)
            ->update(['status' => 'completed']);
    }

    /**
     * Помечает задачу как проваленную и увеличивает счетчик попыток.
     *
     * @param int $jobId
     * @param int $newAttemptCount
     */
    public function markAsFailed(int $jobId, int $newAttemptCount): void
    {
        $data = [
            'status' => 'failed',
            'attempts' => $newAttemptCount,
        ];
        $this->db->queryBuilder()
            ->from(self::TABLE_NAME)
            ->where('id', '=', $jobId)
            ->update($data);
    }

    /**
     * Проверяет, есть ли в очереди ожидающие задачи.
     *
     * @return bool
     */
    public function hasPendingJobs(): bool
    {
        $count = $this->db->queryBuilder()
            ->from(self::TABLE_NAME)
            ->where('status', '=', 'pending')
            ->where('available_at', '<=', gmdate('Y-m-d H:i:s'))
            ->count();

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
        $data = [
            'message_class' => $messageClass,
            'args' => serialize($args),
            'available_at' => gmdate('Y-m-d H:i:s', time() + $delaySeconds),
            'created_at' => gmdate('Y-m-d H:i:s'),
        ];
        $result = $this->db->queryBuilder()->from(self::TABLE_NAME)->insert($data);

        return $result ? $this->db->getInsertId() : null;
    }

    /**
     * Удаляет старые выполненные задачи.
     *
     * @param string $beforeDate Дата в формате 'Y-m-d H:i:s', до которой нужно удалить записи.
     * @return int Количество удаленных строк.
     */
    public function pruneOldJobs(string $beforeDate): int
    {
        $result = $this->db->queryBuilder()
            ->from(self::TABLE_NAME)
            ->where('status', '=', 'completed')
            ->where('created_at', '<', $beforeDate)
            ->delete();

        return $result === false ? 0 : $result;
    }

    /**
     * Создает таблицу в БД.
     */
    public function createTable(): void
    {
        $queryBuilder = $this->db->queryBuilder();
        $table_name = $queryBuilder->getTableName(self::TABLE_NAME);
        $charset_collate = $queryBuilder->getCharsetCollate();

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

        $queryBuilder->runDbDelta($sql);
    }

    /**
     * Удаляет таблицу.
     */
    public function dropTable(): void
    {
        $this->db->queryBuilder()->dropTableIfExists(self::TABLE_NAME);
    }
}