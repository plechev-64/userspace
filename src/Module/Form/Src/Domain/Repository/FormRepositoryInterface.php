<?php

namespace UserSpace\Module\Form\Src\Domain\Repository;

if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для репозитория, управляющего формами в базе данных.
 */
interface FormRepositoryInterface
{
    /**
     * Находит форму по ее типу.
     *
     * @param string $type
     * @return object|null
     */
    public function findByType(string $type): ?object;

    /**
     * Создает или обновляет форму.
     *
     * @param string $type
     * @param array $config
     * @return int|false
     */
    public function createOrUpdate(string $type, array $config): int|false;
}