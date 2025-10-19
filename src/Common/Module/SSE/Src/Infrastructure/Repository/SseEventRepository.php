<?php

namespace UserSpace\Common\Module\SSE\Src\Infrastructure\Repository;

use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Core\Database\QueryBuilderInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Репозиторий для управления SSE-событиями в базе данных.
 */
class SseEventRepository implements SseEventRepositoryInterface
{
    private const TABLE_NAME = 'userspace_sse_events';

    public function __construct(private readonly QueryBuilderInterface $queryBuilder)
    {
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
        $data = [
            'event_type' => $eventType,
            'payload' => wp_json_encode($payload),
            'created_at' => gmdate('Y-m-d H:i:s'),
        ];

        $result = $this->queryBuilder->table(self::TABLE_NAME)->insert($data);

        return $result ? $this->queryBuilder->getWpdb()->insert_id : null;
    }

    /**
     * Находит новые события, начиная с указанного ID.
     *
     * @param int $lastEventId
     * @return array
     */
    public function findNewerThan(int $lastEventId): array
    {
        return $this->queryBuilder->table(self::TABLE_NAME)
            ->where('id', '>', $lastEventId)
            ->orderBy('id', 'ASC')
            ->get();
    }

    /**
     * Удаляет события до указанного ID включительно.
     *
     * @param int $lastEventId
     */
    public function deleteOlderThanOrEqual(int $lastEventId): void
    {
        $this->queryBuilder->table(self::TABLE_NAME)
            ->where('id', '<=', $lastEventId)
            ->delete();
    }

    /**
     * Удаляет старые SSE-события.
     *
     * @param string $beforeDate Дата в формате 'Y-m-d H:i:s', до которой нужно удалить записи.
     * @return int Количество удаленных строк.
     */
    public function pruneOldEvents(string $beforeDate): int
    {
        $result = $this->queryBuilder->table(self::TABLE_NAME)
            ->where('created_at', '<', $beforeDate)
            ->delete();

        return $result === false ? 0 : $result;
    }

    /**
     * Создает таблицу в БД. Этот метод должен вызываться при активации плагина.
     */
    public function createTable(): void
    {
        $table_name = $this->queryBuilder->getTableName(self::TABLE_NAME);
        $charset_collate = $this->queryBuilder->getCharsetCollate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type VARCHAR(255) NOT NULL,
            payload LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        $this->queryBuilder->runDbDelta($sql);
    }

    /**
     * Удаляет таблицу при деактивации плагина.
     */
    public function dropTable(): void
    {
        $this->queryBuilder->dropTableIfExists(self::TABLE_NAME);
    }
}