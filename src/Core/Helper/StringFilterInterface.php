<?php

namespace UserSpace\Core\Helper;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Описывает интерфейс для сервиса-обертки для функций WordPress по обработке строк.
 */
interface StringFilterInterface
{
    /**
     * Переводит и возвращает строку.
     * Обертка для __()
     *
     * @param string $text Текст для перевода.
     * @param string $domain Текстовый домен. Уникальный идентификатор для получения перевода.
     * @return string Переведенный текст.
     */
    public function translate(string $text, string $domain = 'usp'): string;

    /**
     * Очищает строку для безопасного вывода в HTML.
     * Обертка для esc_html()
     *
     * @param string $text Текст для очистки.
     * @return string Очищенный текст.
     */
    public function escHtml(string $text): string;

    /**
     * Очищает строку для безопасного использования в HTML-атрибутах.
     * Обертка для esc_attr()
     *
     * @param string $text Текст для очистки.
     * @return string Очищенный текст.
     */
    public function escAttr(string $text): string;

    /**
     * Очищает URL для безопасного вывода.
     * Обертка для esc_url()
     *
     * @param string $url URL для очистки.
     * @return string Очищенный URL.
     */
    public function escUrl(string $url): string;

    /**
     * Очищает URL для использования в небезопасных контекстах.
     * Обертка для esc_url_raw()
     *
     * @param string $url URL для очистки.
     * @return string Очищенный URL.
     */
    public function escUrlRaw(string $url): string;

    /**
     * Удаляет экранирующие слеши из строки или массива строк.
     * Обертка для wp_unslash()
     *
     * @param string|array<mixed> $value Строка или массив для обработки.
     * @return string|array<mixed>
     */
    public function unslash(string|array $value): string|array;
}