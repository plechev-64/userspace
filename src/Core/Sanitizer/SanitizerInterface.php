<?php

namespace UserSpace\Core\Sanitizer;

interface SanitizerInterface
{
    /**
     * Sanitizes an array of data based on a given configuration.
     *
     * @param array $data The raw data to sanitize (e.g., $_GET, $_POST).
     * @param array $config An associative array where keys are data keys
     *                      and values are sanitation rules (e.g., SanitizerRule::TEXT_FIELD).
     * @return ClearedDataInterface An object containing the sanitized data.
     */
    public function sanitize(array $data, array $config): ClearedDataInterface;
}