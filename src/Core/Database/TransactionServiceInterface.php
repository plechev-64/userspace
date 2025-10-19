<?php

namespace UserSpace\Core\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Описывает интерфейс для управления транзакциями в базе данных.
 */
interface TransactionServiceInterface
{
    /**
     * Начинает новую транзакцию.
     */
    public function beginTransaction(): void;

    /**
     * Фиксирует текущую транзакцию.
     */
    public function commit(): void;

    /**
     * Откатывает текущую транзакцию.
     */
    public function rollback(): void;
}