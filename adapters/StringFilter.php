<?php

namespace UserSpace\Adapters;

// Защита от прямого доступа к файлу
use UserSpace\Core\String\StringFilterInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Сервис-обертка для функций WordPress по обработке строк.
 */
class StringFilter implements StringFilterInterface
{
    public static function sTranslate(string $text, string $domain = 'usp'): string
    {
        return __($text, $domain);
    }

    /**
     * Переводит и возвращает строку.
     * Обертка для __()
     *
     * @param string $text Текст для перевода.
     * @param string $domain Текстовый домен. Уникальный идентификатор для получения перевода.
     * @return string Переведенный текст.
     */
    public function translate(string $text, string $domain = 'usp'): string
    {
        return __($text, $domain);
    }

    /**
     * Очищает строку для безопасного вывода в HTML.
     * Обертка для esc_html()
     *
     * @param string $text Текст для очистки.
     * @return string Очищенный текст.
     */
    public function escHtml(string $text): string
    {
        return esc_html($text);
    }

    /**
     * Очищает строку для безопасного использования в HTML-атрибутах.
     * Обертка для esc_attr()
     *
     * @param string|null $text Текст для очистки.
     * @return string Очищенный текст.
     */
    public function escAttr(?string $text): string
    {
        return esc_attr($text ?? '');
    }

    public function escUrl(string $url): string
    {
        return esc_url($url);
    }

    public function escUrlRaw(string $url): string
    {
        return esc_url_raw($url);
    }

    /**
     * Очищает строку для безопасного вывода в HTML-элементах textarea.
     * Обертка для esc_textarea()
     *
     * @param string|null $text Текст для очистки.
     * @return string Очищенный текст.
     */
    public function escTextarea(?string $text): string
    {
        return esc_textarea($text ?? '');
    }

    /**
     * Удаляет экранирующие слеши из строки или массива строк.
     * Обертка для wp_unslash()
     *
     * @param string|array<mixed> $value Строка или массив для обработки.
     * @return string|array<mixed>
     */
    public function unslash(string|array $value): string|array
    {
        return wp_unslash($value);
    }

    /**
     * @inheritDoc
     */
    public function sanitizeKey(string $key): string
    {
        return sanitize_key($key);
    }

    /**
     * @inheritDoc
     */
    public function sanitizeTextField(string|array $value): string|array
    {
        if (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        }

        return sanitize_text_field($value);
    }

    public function isEmail(string $email): false|string
    {
        return is_email($email);
    }

    public function jsonEncode(mixed $data): false|string
    {
        return wp_json_encode($data);
    }
}