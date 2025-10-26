<?php

namespace UserSpace\Common\Module\Settings\Src\Domain;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для работы с WordPress Transients API.
 */
interface TransientApiInterface
{
    /**
     * Получает значение временной опции.
     * Обертка для get_transient().
     */
    public function get(string $transient): mixed;

    /**
     * Устанавливает значение временной опции.
     * Обертка для set_transient().
     */
    public function set(string $transient, mixed $value, int $expiration = 0): bool;

    /**
     * Удаляет временную опцию.
     * Обертка для delete_transient().
     */
    public function delete(string $transient): bool;
}