<?php

namespace UserSpace\Core\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Сервис для управления транзакциями в базе данных.
 */
class TransactionService implements TransactionServiceInterface
{
    private readonly DatabaseConnectionInterface $db;

    public function __construct(DatabaseConnectionInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction(): void
    {
        $this->db->query('START TRANSACTION');
    }

    /**
     * @inheritDoc
     */
    public function commit(): void
    {
        $this->db->query('COMMIT');
    }

    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
        $this->db->query('ROLLBACK');
    }
}