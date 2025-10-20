<?php

namespace UserSpace\WpAdapter;

use UserSpace\Core\Admin\AdminApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Адаптер для функций административной панели WordPress.
 */
class AdminApi implements AdminApiInterface
{
    public function isAdmin(): bool
    {
        return is_admin();
    }

    public function adminUrl(string $path = '', string $scheme = 'admin'): string
    {
        return admin_url($path, $scheme);
    }

    public function addMenuPage(string $pageTitle, string $menuTitle, string $capability, string $menuSlug, callable|string $callback = '', string $iconUrl = '', ?int $position = null): string|false
    {
        return add_menu_page($pageTitle, $menuTitle, $capability, $menuSlug, $callback, $iconUrl, $position);
    }

    public function addSubmenuPage(?string $parentSlug, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, callable|string $callback = '', ?int $position = null): string|false
    {
        return add_submenu_page($parentSlug, $pageTitle, $menuTitle, $capability, $menuSlug, $callback, $position);
    }

    public function getAdminPageTitle(): string
    {
        return get_admin_page_title();
    }
}