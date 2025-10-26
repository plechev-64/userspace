<?php

namespace UserSpace\Common\Module\Media\Src\Infrastructure;

use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileRepositoryInterface;
use UserSpace\Core\Database\DatabaseConnectionInterface;

class TemporaryFileRepository implements TemporaryFileRepositoryInterface
{
    private const TABLE_NAME = 'usp_temporary_files';

    private string $tableName;

    public function __construct(
        private readonly DatabaseConnectionInterface $db
    )
    {
        $this->tableName = $this->db->getPrefix() . self::TABLE_NAME;
    }

    public function createTable(): void
    {
        $charsetCollate = $this->db->getCharsetCollate();

        $sql = "CREATE TABLE {$this->tableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            attachment_id BIGINT(20) UNSIGNED NOT NULL,
            uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY attachment_id (attachment_id)
        ) $charsetCollate;";

        $this->db->query($sql);
    }

    public function dropTable(): void
    {
        $this->db->query("DROP TABLE IF EXISTS {$this->tableName};");
    }

    public function add(int $attachmentId): void
    {
        $this->db->insert(
            $this->tableName,
            ['attachment_id' => $attachmentId],
            ['%d']
        );
    }

    public function remove(array $attachmentIds): void
    {
        if (empty($attachmentIds)) {
            return;
        }

        // Убедимся, что все элементы - целые числа
        $ids = array_map('intval', $attachmentIds);
        $placeholders = implode(', ', array_fill(0, count($ids), '%d'));

        // Передаем SQL и массив ID напрямую в метод query, который сам вызовет prepare.
        // Используем call_user_func_array для распаковки массива ID в отдельные аргументы.
        $query = "DELETE FROM {$this->tableName} WHERE attachment_id IN ($placeholders)";
        call_user_func_array([$this->db, 'query'], array_merge([$query], $ids));
    }

    public function findOlderThan(int $hours): array
    {
        // Передаем SQL и параметр напрямую в метод getCol.
        $results = $this->db->getCol(
            "SELECT attachment_id FROM {$this->tableName} WHERE uploaded_at < NOW() - INTERVAL %d HOUR",
            $hours
        );

        return array_map('intval', $results);
    }
}