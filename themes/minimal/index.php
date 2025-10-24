<?php

namespace UserSpace\Theme\Minimal;

use UserSpace\Core\Addon\AddonManagerInterface;

if (!defined('ABSPATH')) {
    exit;
}

add_action('userspace_loaded', function (AddonManagerInterface $addonManager) {
    $addonManager->register(MinimalTheme::class);

}, 10, 1);