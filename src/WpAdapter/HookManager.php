<?php

namespace UserSpace\WpAdapter;

use UserSpace\Core\Hooks\HookManagerInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Адаптер для системы хуков WordPress.
 */
class HookManager implements HookManagerInterface
{
    public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        add_action($hookName, $callback, $priority, $acceptedArgs);
    }

    public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        add_filter($hookName, $callback, $priority, $acceptedArgs);
    }

    public function doAction(string $hookName, ...$args): void
    {
        do_action($hookName, ...$args);
    }

    public function applyFilters(string $hookName, mixed $value, ...$args): mixed
    {
        return apply_filters($hookName, $value, ...$args);
    }

    public function didAction(string $hookName): int
    {
        return did_action($hookName);
    }
}