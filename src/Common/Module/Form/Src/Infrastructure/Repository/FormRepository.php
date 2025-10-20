<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Repository;

use UserSpace\Common\Module\Form\Src\Domain\Repository\FormRepositoryInterface;
use UserSpace\Core\Database\DatabaseConnectionInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Репозиторий для управления формами в базе данных.
 */
class FormRepository implements FormRepositoryInterface
{
    private const TABLE_NAME = 'userspace_forms';

    private readonly DatabaseConnectionInterface $db;

    public function __construct(DatabaseConnectionInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Находит форму по ее типу.
     *
     * @param string $type
     * @return object|null
     */
    public function findByType(string $type): ?object
    {
        return $this->db->queryBuilder()
            ->from(self::TABLE_NAME)
            ->where('type', '=', $type)
            ->first();
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
        $existing = $this->findByType($type);

        $data = [
            'type' => $type,
            'config' => wp_json_encode($config),
        ];

        if ($existing) {
            return $this->db->queryBuilder()
                ->from(self::TABLE_NAME)
                ->where('id', '=', $existing->id)
                ->update($data);
        }

        $data['created_at'] = current_time('mysql', 1);
        return $this->db->queryBuilder()->from(self::TABLE_NAME)->insert($data);
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
			type VARCHAR(100) NOT NULL,
			config LONGTEXT NOT NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY type (type)
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