<?php

namespace UserSpace\Core\Helper;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Сервис-обертка для функций WordPress по обработке строк.
 */
class StringFilter implements StringFilterInterface
{
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
     * @param string $text Текст для очистки.
     * @return string Очищенный текст.
     */
    public function escAttr(string $text): string
    {
        return esc_attr($text);
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
}