<?php

namespace UserSpace\Core;

interface SecurityHelperInterface
{
    /**
     * Генерирует подпись для массива данных.
     *
     * @param array $data Массив данных для подписи.
     * @return string Сгенерированная подпись.
     */
    public function sign(array $data): string;

    /**
     * Проверяет подпись массива данных.
     *
     * @param array $data Массив данных, для которых проверяется подпись.
     * @param string $signature Подпись для проверки.
     * @return bool True, если подпись валидна, false в противном случае.
     */
    public function validate(array $data, string $signature): bool;

    /**
     * Возвращает секретный ключ безопасности.
     * @return string
     */
    public function getSecurityKey(): string;
}