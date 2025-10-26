<?php

namespace UserSpace\Core\Database;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для взаимодействия с базой данных.
 * Абстрагирует конкретную реализацию (например, wpdb) для повышения тестируемости и гибкости.
 */
interface DatabaseConnectionInterface
{
    /**
     * Возвращает новый экземпляр конструктора запросов.
     *
     * @return QueryBuilderInterface
     */
    public function queryBuilder(): QueryBuilderInterface;

    /**
     * Выполняет запрос и возвращает массив результатов.
     *
     * @param string $query SQL-запрос с плейсхолдерами.
     * @param mixed ...$args Аргументы для подстановки в запрос.
     * @return array
     */
    public function getResults(string $query, ...$args): array;

    /**
     * Выполняет запрос и возвращает одну строку.
     *
     * @param string $query SQL-запрос с плейсхолдерами.
     * @param mixed ...$args Аргументы для подстановки в запрос.
     * @return object|null
     */
    public function getRow(string $query, ...$args): ?object;

    /**
     * Выполняет запрос и возвращает одно значение.
     *
     * @param string $query SQL-запрос с плейсхолдерами.
     * @param mixed ...$args Аргументы для подстановки в запрос.
     * @return mixed
     */
    public function getVar(string $query, ...$args): mixed;

    /**
     * Выполняет "сырой" SQL-запрос.
     *
     * @param string $query SQL-запрос с плейсхолдерами.
     * @param mixed ...$args Аргументы для подстановки в запрос.
     * @return int|false
     */
    public function query(string $query, ...$args): int|false;

    /**
     * Вставляет строку в таблицу.
     *
     * @param string $table Имя таблицы.
     * @param array $data Данные для вставки (column => value).
     * @return int|false
     */
    public function insert(string $table, array $data): int|false;

    /**
     * Возвращает строку кодировки и сопоставления для создания таблицы.
     *
     * @return string
     */
    public function getCharsetCollate(): string;

    /**
     * Выполняет SQL для создания или обновления таблицы с помощью dbDelta.
     *
     * @param string $schemaSql SQL-схема таблицы.
     */
    public function runDbDelta(string $schemaSql): void;

    /**
     * Удаляет таблицу, если она существует.
     *
     * @param string $tableName Имя таблицы (без префикса).
     */
    public function dropTableIfExists(string $tableName): void;

    /**
     * Возвращает полное имя таблицы с префиксом.
     *
     * @param string $tableName Имя таблицы без префикса.
     * @return string
     */
    public function getTableName(string $tableName): string;

    /**
     * Экранирует строку для использования в SQL-запросе в части LIKE.
     *
     * @param string $text Текст для экранирования.
     * @return string
     */
    public function escLike(string $text): string;

    /**
     * Возвращает ID, сгенерированный последним запросом INSERT.
     *
     * @return int
     */
    public function getInsertId(): int;

    /**
     * Возвращает префикс таблиц базы данных WordPress.
     *
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Возвращает имя таблицы usermeta с префиксом.
     *
     * @return string
     */
    public function getUsermetaTableName(): string;

    /**
     * Возвращает имя таблицы users с префиксом.
     *
     * @return string
     */
    public function getUsersTableName(): string;

    /**
     * Возвращает имя таблицы posts с префиксом.
     *
     * @return string
     */
    public function getPostsTableName(): string;

    /**
     * Возвращает имя таблицы options с префиксом.
     *
     * @return string
     */
    public function getOptionsTableName(): string;

    /**
     * Возвращает сервис для управления транзакциями.
     *
     * @return TransactionServiceInterface
     */
    public function transaction(): TransactionServiceInterface;
}