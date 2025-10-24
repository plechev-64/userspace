<?php

namespace UserSpace\Theme\Minimal\Service;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabLocationManager;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Регистрирует локации для вкладок, специфичные для темы "Minimal".
 */
class TabLocationService
{
    public function __construct(
        private readonly TabLocationManager    $tabLocationManager,
        private readonly StringFilterInterface $stringFilter
    )
    {
    }

    /**
     * Регистрирует доступные в теме локации для меню.
     */
    public function registerThemeLocations(): void
    {
        $this->tabLocationManager->registerLocation(
            'sidebar',
            $this->stringFilter->translate('Sidebar Menu', 'usp')
        );
    }
}