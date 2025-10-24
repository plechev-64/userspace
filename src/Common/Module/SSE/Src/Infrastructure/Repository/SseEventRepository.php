<?php

namespace UserSpace\Common\Module\SSE\Src\Infrastructure\Repository;

use UserSpace\Common\Module\Settings\Src\Domain\TransientApiInterface;
use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Core\Database\DatabaseConnectionInterface;
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

    public function __construct(
        private readonly DatabaseConnectionInterface $db,
        private readonly TransientApiInterface       $transientApi
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function create(string $eventType, array $payload, ?int $userId = null): ?int
    {
        $data = [
            'event_type' => $eventType,
            'payload' => wp_json_encode($payload),
            'created_at' => gmdate('Y-m-d H:i:s'),
            'user_id' => $userId,
        ];

        $result = $this->db->queryBuilder()->from(self::TABLE_NAME)->insert($data);

        return $result ? $this->db->getInsertId() : null;
    }

    /**
     * @inheritDoc
     */
    public function findNewerThan(int $lastEventId, ?int $userId): array
    {
        // Ключ кэша теперь зависит от пользователя. Для анонимов userId будет 0.
        $userIdCacheKey = $userId > 0 ? $userId : '0';
        $cacheKey = 'usp_sse_events_after_' . $lastEventId . '_user_' . $userIdCacheKey;

        $cachedEvents = $this->transientApi->get($cacheKey);
        if ($cachedEvents !== false && is_array($cachedEvents)) {
            return $cachedEvents;
        }

        $builder = $this->db->queryBuilder()
            ->from(self::TABLE_NAME)
            ->where('id', '>', $lastEventId)
            ->orderBy('id', 'ASC');

        // Пользователь получает свои личные события И глобальные (user_id IS NULL)
        if ($userId > 0) {
            $builder->where(function (QueryBuilderInterface $query) use ($userId) {
                $query->where('user_id', '=', $userId)
                    ->orWhereNull('user_id');
            });
        } else {
            // Анонимный пользователь получает только глобальные события
            $builder->whereNull('user_id');
        }

        $events = $builder->get();

        $this->transientApi->set($cacheKey, $events, 2);

        return $events;
    }

    /**
     * Удаляет события до указанного ID включительно.
     *
     * @param int $lastEventId
     */
    public function deleteOlderThanOrEqual(int $lastEventId): void
    {
        $this->db->queryBuilder()
            ->from(self::TABLE_NAME)
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
        $result = $this->db->queryBuilder()
            ->from(self::TABLE_NAME)
            ->where('created_at', '<', $beforeDate)
            ->delete();

        return $result === false ? 0 : $result;
    }

    /**
     * Создает таблицу в БД. Этот метод должен вызываться при активации плагина.
     */
    public function createTable(): void
    {
        $table_name = $this->db->getTableName(self::TABLE_NAME);
        $charset_collate = $this->db->getCharsetCollate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type VARCHAR(255) NOT NULL,
            user_id BIGINT(20) UNSIGNED DEFAULT NULL,
            payload LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) {$charset_collate};";

        $this->db->runDbDelta($sql);
    }

    /**
     * Удаляет таблицу при деактивации плагина.
     */
    public function dropTable(): void
    {
        $this->db->dropTableIfExists(self::TABLE_NAME);
    }

    public function findLatest(?int $userId): ?object
    {
        $builder = $this->db->queryBuilder()
            ->from(self::TABLE_NAME)
            ->orderBy('id', 'DESC')
            ->limit(1);

        if ($userId > 0) {
            // События для конкретного пользователя ИЛИ общие события
            $builder->where(function (QueryBuilderInterface $query) use ($userId) {
                $query->where('user_id', '=', $userId)
                    ->orWhereNull('user_id');
            });
        } else {
            // Только общие события для гостей
            $builder->whereNull('user_id');
        }

        return $builder->first();
    }
}