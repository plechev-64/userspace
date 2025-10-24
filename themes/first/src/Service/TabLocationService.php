<?php

namespace UserSpace\Theme\First\Service;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabLocationManager;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Управляет регистрацией мест вывода вкладок для темы "First".
 */
class TabLocationService
{
    public function __construct(
        private readonly TabLocationManager    $tabLocationManager,
        private readonly StringFilterInterface $stringFilter
    )
    {
    }

    public function registerThemeLocations(): void
    {
        $this->tabLocationManager->registerLocation('sidebar', $this->stringFilter->translate('Sidebar', 'usp'));
        $this->tabLocationManager->registerLocation('header', $this->stringFilter->translate('Header Menu', 'usp'));
    }
}