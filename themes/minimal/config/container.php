<?php

use UserSpace\Theme\Minimal\TabLocationService;
use UserSpace\Theme\Minimal\ViewDataProvider;

return [
    'parameters' => [
        'app.templates' => [
            'tab_menu' => dirname(__DIR__) . '/views/parts/tab-menu.php',
        ],
    ],
    'definitions' => [
        TabLocationService::class => fn(\UserSpace\Core\ContainerInterface $container) => $container->get(TabLocationService::class),
        ViewDataProvider::class => fn(\UserSpace\Core\ContainerInterface $container) => $container->get(ViewDataProvider::class),
    ],
];