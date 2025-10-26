<?php

declare(strict_types=1);

namespace UserSpace\Core\Addon\Theme;

use UserSpace\Core\Addon\AddonManagerInterface;

if (!defined('ABSPATH')) {
    exit;
}

interface ThemeManagerInterface extends AddonManagerInterface
{
    /**
     * Регистрирует класс темы.
     * Этот метод вызывается из файла index.php темы.
     *
     * @param string $themeClassName Полное имя класса темы.
     * @return void
     */
    public function register(string $themeClassName): void;

    /**
     * Сканирует директорию и возвращает список доступных тем.
     *
     * @return array<string, string> Ассоциативный массив [dir_name => theme_name].
     */
    public function discoverThemes(): array;

    /**
     * Загружает точку входа активной темы для регистрации её сервисов и хуков.
     * Должен вызываться на раннем этапе, например, 'plugins_loaded'.
     */
    public function initialize(): void;

    public function getActiveTheme(): ?ThemeInterface;

    /**
     * Рендерит активную тему личного кабинета.
     *
     * @return string
     */
    public function renderActiveTheme(): string;
}