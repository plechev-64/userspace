<?php

namespace UserSpace\Theme\First\Service;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabLocationManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;
use UserSpace\Core\Container;
use UserSpace\Core\ContainerInterface;

/**
 * Регистрирует сервисы, специфичные для данной темы.
 */
class ThemeServiceProvider
{
    private ContainerInterface $themeContainer;

    public function __construct(ContainerInterface $pluginContainer)
    {
        $this->themeContainer = new Container();
        $this->register($pluginContainer);
    }

    private function register(ContainerInterface $pluginContainer): void
    {
        $this->themeContainer->set(TabLocationService::class, fn() => new TabLocationService(
            $pluginContainer->get(TabLocationManager::class)
        ));

        $this->themeContainer->set(ViewDataProvider::class, fn() => new ViewDataProvider(
            $pluginContainer->get(TabManager::class),
            $pluginContainer->get(TabLocationManager::class)
        ));
    }

    public function getContainer(): ContainerInterface
    {
        return $this->themeContainer;
    }
}