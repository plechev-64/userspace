<?php

namespace UserSpace\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для взаимодействия с функциями административной панели WordPress.
 * Абстрагирует такие функции, как add_menu_page, admin_url и т.д.
 */
interface AdminApiInterface
{
    /**
     * Проверяет, является ли текущий запрос запросом к административной панели.
     * Обертка для is_admin().
     *
     * @return bool
     */
    public function isAdmin(): bool;

    /**
     * Формирует URL для административной панели.
     * Обертка для admin_url().
     *
     * @param string $path Опциональный путь.
     * @param string $scheme Схема для использования.
     * @return string
     */
    public function adminUrl(string $path = '', string $scheme = 'admin'): string;

    /**
     * Добавляет страницу верхнего уровня в меню административной панели.
     * Обертка для add_menu_page().
     *
     * @param string $pageTitle
     * @param string $menuTitle
     * @param string $capability
     * @param string $menuSlug
     * @param callable|string $callback
     * @param string $iconUrl
     * @param int|null $position
     * @return string|false
     */
    public function addMenuPage(string $pageTitle, string $menuTitle, string $capability, string $menuSlug, callable|string $callback = '', string $iconUrl = '', ?int $position = null): string|false;

    /**
     * Добавляет подстраницу в меню административной панели.
     * Обертка для add_submenu_page().
     *
     * @param string|null $parentSlug
     * @param string $pageTitle
     * @param string $menuTitle
     * @param string $capability
     * @param string $menuSlug
     * @param callable|string $callback
     * @param int|null $position
     * @return string|false
     */
    public function addSubmenuPage(?string $parentSlug, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, callable|string $callback = '', ?int $position = null): string|false;

    /**
     * Получает заголовок текущей страницы административной панели.
     * Обертка для get_admin_page_title().
     *
     * @return string
     */
    public function getAdminPageTitle(): string;
}