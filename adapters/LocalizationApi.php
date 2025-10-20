<?php

namespace Adapters;

use UserSpace\Core\Localization\LocalizationApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

class LocalizationApi implements LocalizationApiInterface
{
    public function loadPluginTextdomain(string $domain, string $pluginRelPath): bool
    {
        return load_plugin_textdomain($domain, false, $pluginRelPath);
    }
}