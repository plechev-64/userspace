<?php

namespace UserSpace\Theme\First\Service;

use UserSpace\Core\Tabs\TabLocationManager;
use UserSpace\Core\Tabs\TabManager;

/**
 * Готовит данные для передачи в шаблоны темы.
 */
class ViewDataProvider
{
    public function __construct(
        private readonly TabManager $tabManager,
        private readonly TabLocationManager $tabLocationManager
    ) {
    }

    /**
     * Собирает все необходимые данные для страницы личного кабинета.
     *
     * @param int $viewedUserId
     * @return array
     */
    public function getAccountPageData(int $viewedUserId): array
    {
        $tabsByLocation = [];
        foreach (array_keys($this->tabLocationManager->getRegisteredLocations()) as $location) {
            $tabsByLocation[$location] = $this->tabManager->getTabs($viewedUserId, $location);
        }

        return [
            'tabs_by_location'     => $tabsByLocation,
            'all_tabs_for_content' => $this->tabManager->getAllRegisteredTabs(),
        ];
    }
}