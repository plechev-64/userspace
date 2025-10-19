<?php

namespace UserSpace\Common\Module\Queue\Src\Infrastructure;

use UserSpace\Core\Database\TransactionServiceInterface;
use UserSpace\Core\Database\QueryBuilder;
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

    public function __construct(
        private readonly QueryBuilder                $queryBuilder,
        private readonly TransactionServiceInterface $transactionService
    )
    {
    }

    /**
     * Находит одну ожидающую задачу, блокирует ее для обработки и помечает как 'in_progress'.
     *
     * @return \stdClass|null Объект задачи или null, если задач нет.
     */
    public function findAndLockOnePendingJob(): ?\stdClass
    {
        $this->transactionService->beginTransaction();

        $query = "SELECT * FROM {$this->queryBuilder->getTableName(self::TABLE_NAME)} WHERE status = %s AND available_at <= %s ORDER BY id ASC LIMIT 1 FOR UPDATE";
        $job = $this->queryBuilder->firstRaw($query, 'pending', gmdate('Y-m-d H:i:s'));

        if (!$job) {
            $this->transactionService->commit();
            return null;
        }

        $this->queryBuilder->table(self::TABLE_NAME)->where('id', '=', $job->id)->update(['status' => 'in_progress']);
        $this->transactionService->commit();

        return $job;
    }

    /**
     * Помечает задачу как успешно выполненную.
     *
     * @param int $jobId
     */
    public function markAsCompleted(int $jobId): void
    {
        $this->queryBuilder->table(self::TABLE_NAME)
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
        $this->queryBuilder->table(self::TABLE_NAME)
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
        $count = $this->queryBuilder->table(self::TABLE_NAME)
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
        $result = $this->queryBuilder->table(self::TABLE_NAME)->insert($data);

        return $result ? $this->queryBuilder->getWpdb()->insert_id : null;
    }

    /**
     * Удаляет старые выполненные задачи.
     *
     * @param string $beforeDate Дата в формате 'Y-m-d H:i:s', до которой нужно удалить записи.
     * @return int Количество удаленных строк.
     */
    public function pruneOldJobs(string $beforeDate): int
    {
        $result = $this->queryBuilder->table(self::TABLE_NAME)
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
        $table_name = $this->queryBuilder->getTableName(self::TABLE_NAME);
        $charset_collate = $this->queryBuilder->getCharsetCollate();

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

        $this->queryBuilder->runDbDelta($sql);
    }

    /**
     * Удаляет таблицу.
     */
    public function dropTable(): void
    {
        $this->queryBuilder->dropTableIfExists(self::TABLE_NAME);
    }
}