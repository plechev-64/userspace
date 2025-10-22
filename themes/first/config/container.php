<?php

use UserSpace\Theme\First\TabLocationService;
use UserSpace\Theme\First\ViewDataProvider;

return [
    'parameters' => [
        'app.templates' => [
            'tab_menu' => dirname(__DIR__) . '/views/parts/tab-menu.php',
        ],
    ],
    'definitions' => [
        // Регистрируем сервисы, специфичные для этой темы.
        TabLocationService::class => fn(\UserSpace\Core\ContainerInterface $container) => $container->get(TabLocationService::class),
        ViewDataProvider::class => fn(\UserSpace\Core\ContainerInterface $container) => $container->get(ViewDataProvider::class),
    ],
];