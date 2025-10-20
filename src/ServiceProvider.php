<?php

namespace UserSpace;

use UserSpace\Core\ContainerInterface;
use UserSpace\Core\Theme\ThemeManager;

/**
 * Регистрирует сервисы плагина в DI-контейнере.
 */
class ServiceProvider
{
    public function register(ContainerInterface $container): void
    {
        // 1. Загружаем основной конфиг плагина
        $mainConfig = require USERSPACE_PLUGIN_DIR . 'config/container.php';
        $this->registerConfig($container, $mainConfig);
        // 2. Загружаем конфиг активной темы
        $themeManager = $container->get(ThemeManager::class);
        $themeConfig = $themeManager->loadActiveThemeConfig();
        $this->registerConfig($container, $themeConfig);

    }

    private function registerConfig($container, array $config): void
    {
        // Регистрируем параметры
        if (isset($config['parameters']) && is_array($config['parameters'])) {
            foreach ($config['parameters'] as $key => $value) {
                $container->set($key, fn() => $value);
            }
        }

        // Регистрируем определения сервисов
        if (isset($config['definitions']) && is_array($config['definitions'])) {
            foreach ($config['definitions'] as $id => $definition) {
                $container->set($id, $definition);
            }
        }
    }
}