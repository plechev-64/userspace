<?php

namespace UserSpace\WpAdapter;

use UserSpace\Core\WpApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

class WpApi implements WpApiInterface
{
    public function isWpError(mixed $thing): bool
    {
        return is_wp_error($thing);
    }

    public function isDoingAjax(): bool
    {
        return wp_doing_ajax();
    }

    public function isDoingCron(): bool
    {
        return wp_doing_cron();
    }

    public function safeRedirect(string $location, int $status = 302): void
    {
        wp_safe_redirect($location, $status);
    }
}