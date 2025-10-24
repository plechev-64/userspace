<?php

namespace UserSpace\Core\Sanitizer;

interface ClearedDataInterface
{
    /**
     * Retrieves a sanitized value by key.
     *
     * @param string $key The key of the data.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The sanitized value or the default value.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Checks if a key exists in the sanitized data.
     *
     * @param string $key The key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Returns all sanitized data as an array.
     *
     * @return array All sanitized data.
     */
    public function all(): array;
}