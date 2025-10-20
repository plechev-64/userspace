<?php

namespace UserSpace\WpAdapter;

use UserSpace\Core\Database\DatabaseConnectionInterface;
use UserSpace\Core\Database\QueryBuilder;
use UserSpace\Core\Database\QueryBuilderInterface;
use UserSpace\Core\Database\TransactionService;
use UserSpace\Core\Database\TransactionServiceInterface;
use wpdb;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Адаптер для класса wpdb, реализующий DatabaseConnectionInterface.
 */
class DatabaseConnection implements DatabaseConnectionInterface
{
    private readonly wpdb $wpdb;
    private ?TransactionServiceInterface $transactionService = null;

    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function queryBuilder(): QueryBuilderInterface
    {
        return new QueryBuilder($this);
    }

    public function getResults(string $query, ...$args): array
    {
        if (empty($args)) {
            return $this->wpdb->get_results($query);
        }
        return $this->wpdb->get_results($this->wpdb->prepare($query, ...$args));
    }

    public function getRow(string $query, ...$args): ?object
    {
        if (empty($args)) {
            return $this->wpdb->get_row($query);
        }
        return $this->wpdb->get_row($this->wpdb->prepare($query, ...$args));
    }

    public function getVar(string $query, ...$args): mixed
    {
        if (empty($args)) {
            return $this->wpdb->get_var($query);
        }
        return $this->wpdb->get_var($this->wpdb->prepare($query, ...$args));
    }

    public function query(string $query, ...$args): int|false
    {
        if (empty($args)) {
            return $this->wpdb->query($query);
        }
        return $this->wpdb->query($this->wpdb->prepare($query, ...$args));
    }

    public function insert(string $table, array $data): int|false
    {
        return $this->wpdb->insert($table, $data);
    }

    public function getCharsetCollate(): string
    {
        return $this->wpdb->get_charset_collate();
    }

    public function runDbDelta(string $schemaSql): void
    {
        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
        dbDelta($schemaSql);
    }

    public function dropTableIfExists(string $tableName): void
    {
        $fullTableName = $this->getTableName($tableName);
        $this->query("DROP TABLE IF EXISTS {$fullTableName}");
    }

    public function getTableName(string $tableName): string
    {
        if (str_starts_with($tableName, $this->wpdb->prefix)) {
            return $tableName;
        }

        return $this->wpdb->prefix . $tableName;
    }

    public function escLike(string $text): string
    {
        return $this->wpdb->esc_like($text);
    }

    public function getInsertId(): int
    {
        return (int)$this->wpdb->insert_id;
    }

    public function getPrefix(): string
    {
        return $this->wpdb->prefix;
    }

    public function getUsermetaTableName(): string
    {
        return $this->wpdb->usermeta;
    }

    public function getUsersTableName(): string
    {
        return $this->wpdb->users;
    }

    public function getPostsTableName(): string
    {
        return $this->wpdb->posts;
    }

    public function getOptionsTableName(): string
    {
        return $this->wpdb->options;
    }

    public function transaction(): TransactionServiceInterface
    {
        if ($this->transactionService === null) {
            $this->transactionService = new TransactionService($this);
        }

        return $this->transactionService;
    }
}