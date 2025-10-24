<?php

namespace UserSpace\Theme\First;

use UserSpace\Common\Addon\AddonManagerInterface;

if (!defined('ABSPATH')) {
    exit;
}

add_action('userspace_loaded', function (AddonManagerInterface $addonManager) {
    $addonManager->register(FirstTheme::class);
}, 10, 1);