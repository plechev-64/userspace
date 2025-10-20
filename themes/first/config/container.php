<?php

use UserSpace\Theme\First\Service\TabLocationService;
use UserSpace\Theme\First\Service\ViewDataProvider;

return [
    'parameters' => [
        // Переопределяем путь к шаблону меню. Теперь он берется из темы.
        'app.templates' => [
            'tab_menu' => __DIR__ . '/../views/tab-menu.php',
        ],
    ],
    'definitions' => [
        // Регистрируем сервисы, специфичные для этой темы.
        TabLocationService::class => fn(\UserSpace\Core\ContainerInterface $container) => $container->get(TabLocationService::class),
        ViewDataProvider::class => fn(\UserSpace\Core\ContainerInterface $container) => $container->get(ViewDataProvider::class),
    ],
];