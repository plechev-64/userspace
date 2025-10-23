<?php

declare(strict_types=1);

namespace UserSpace\Core\Theme;

if ( ! defined('ABSPATH')) {
	exit;
}

interface ThemeManagerInterface
{
	/**
	 * Регистрирует класс темы.
	 * Этот метод вызывается из файла index.php темы.
	 *
	 * @param string $themeClassName Полное имя класса темы.
	 * @return ThemeInterface|null
	 */
	public function register(string $themeClassName): ?ThemeInterface;

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
	public function loadActiveTheme(): void;

	/**
	 * Загружает конфигурационный файл активной темы, если он существует.
	 *
	 * @return array Конфигурация темы или пустой массив.
	 */
	public function loadActiveThemeConfig(): array;

	public function getActiveTheme(): ?ThemeInterface;

	/**
	 * Рендерит активную тему личного кабинета.
	 *
	 * @return string
	 */
	public function renderActiveTheme(): string;
}