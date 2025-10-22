<?php

namespace UserSpace\Core\String;

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
     * Статически переводит и возвращает строку.
     * Обертка для __() для использования в статическом контексте.
     *
     * @param string $text Текст для перевода.
     * @param string $domain Текстовый домен.
     * @return string Переведенный текст.
     */
    public static function sTranslate(string $text, string $domain = 'usp'): string;

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
     * Очищает строку для безопасного вывода в HTML-элементах textarea.
     * Обертка для esc_textarea()
     *
     * @param string|null $text Текст для очистки.
     * @return string Очищенный текст.
     */
    public function escTextarea(?string $text): string;

    /**
     * Удаляет экранирующие слеши из строки или массива строк.
     * Обертка для wp_unslash()
     *
     * @param string|array<mixed> $value Строка или массив для обработки.
     * @return string|array<mixed>
     */
    public function unslash(string|array $value): string|array;

    /**
     * Очищает ключ (например, для мета-полей или опций).
     * Обертка для sanitize_key().
     *
     * @param string $key Ключ для санации.
     * @return string
     */
    public function sanitizeKey(string $key): string;

    /**
     * Очищает текстовое поле или массив полей.
     * Обертка для sanitize_text_field().
     * @param string|array<string> $value Значение для санации.
     * @return string|array<string>
     */
    public function sanitizeTextField(string|array $value): string|array;

    /**
     * Проверяет, является ли строка валидным email-адресом.
     * Обертка для is_email().
     *
     * @param string $email
     * @return string|false Валидный email или false.
     */
    public function isEmail(string $email): string|false;

    /**
     * Кодирует переменную в формат JSON.
     * Обертка для wp_json_encode().
     *
     * @param mixed $data
     * @return string|false
     */
    public function jsonEncode(mixed $data): string|false;
}