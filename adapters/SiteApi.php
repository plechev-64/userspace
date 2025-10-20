<?php

namespace Adapters;

use UserSpace\Core\SiteApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

class SiteApi implements SiteApiInterface
{
    public function homeUrl(string $path = ''): string
    {
        return home_url($path);
    }
}