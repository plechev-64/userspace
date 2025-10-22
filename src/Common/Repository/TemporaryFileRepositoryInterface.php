<?php

namespace UserSpace\Common\Repository;

interface TemporaryFileRepositoryInterface
{
    /**
     * Создает таблицу для временных файлов.
     */
    public function createTable(): void;

    /**
     * Удаляет таблицу для временных файлов.
     */
    public function dropTable(): void;

    /**
     * Добавляет ID вложения в таблицу временных файлов.
     */
    public function add(int $attachmentId): void;

    /**
     * Удаляет один или несколько ID вложений из таблицы временных файлов.
     */
    public function remove(array $attachmentIds): void;

    /**
     * Находит ID вложений, которые старше указанного количества часов.
     * @return int[]
     */
    public function findOlderThan(int $hours): array;
}