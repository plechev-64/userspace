<?php

namespace UserSpace\Core;

class SecurityHelper
{
    private const SECURITY_KEY_OPTION = 'usp_security_key';

    /**
     * Получает или генерирует уникальный ключ безопасности для сайта.
     * @return string
     */
    public function getSecurityKey(): string
    {
        $key = get_option(self::SECURITY_KEY_OPTION);

        if (empty($key)) {
            $key = wp_generate_password(64, true, true);
            update_option(self::SECURITY_KEY_OPTION, $key);
        }

        return $key;
    }

    /**
     * Подписывает массив данных.
     *
     * @param array $data Данные для подписи.
     *
     * @return string Подпись.
     */
    public function sign(array $data): string
    {
        ksort($data);
        $serializedData = wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash_hmac('sha256', $serializedData, $this->getSecurityKey());
    }

    /**
     * Проверяет подпись для переданных данных.
     *
     * @param array $data Данные, которые были подписаны.
     * @param string $signature Подпись для проверки.
     *
     * @return bool True, если подпись верна, иначе false.
     */
    public function validate(array $data, string $signature): bool
    {
        if (empty($signature)) {
            return false;
        }
        return hash_equals($this->sign($data), $signature);
    }
}