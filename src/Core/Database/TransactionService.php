<?php

namespace UserSpace\Core\Database;

use wpdb;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Сервис для управления транзакциями в базе данных.
 */
class TransactionService implements TransactionServiceInterface
{
    private readonly wpdb $wpdb;

    public function __construct(QueryBuilderInterface $queryBuilder)
    {
        $this->wpdb = $queryBuilder->getWpdb();
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction(): void
    {
        $this->wpdb->query('START TRANSACTION');
    }

    /**
     * @inheritDoc
     */
    public function commit(): void
    {
        $this->wpdb->query('COMMIT');
    }

    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
        $this->wpdb->query('ROLLBACK');
    }
}