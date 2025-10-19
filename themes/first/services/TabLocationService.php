<?php

namespace UserSpace\Theme\First\Service;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabLocationManager;

/**
 * Управляет регистрацией мест вывода вкладок для темы "First".
 */
class TabLocationService
{
    public function __construct(private readonly TabLocationManager $tabLocationManager)
    {
    }

    public function registerThemeLocations(): void
    {
        $this->tabLocationManager->registerLocation('sidebar', __('Sidebar', 'usp'));
        $this->tabLocationManager->registerLocation('header', __('Header Menu', 'usp'));
    }
}