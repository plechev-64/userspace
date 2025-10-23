<?php

namespace UserSpace;

use UserSpace\Core\Container\ContainerInterface;
use UserSpace\Core\Container\Params;
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

        $this->registerParameters($container, $mainConfig['parameters']);
        $this->registerDefinitions($container, $mainConfig['definitions']);

        // 2. Загружаем конфиг активной темы
        $themeManager = $container->get(ThemeManager::class);
        $themeManager->loadActiveTheme();
        $themeConfig = $themeManager->loadActiveThemeConfig();

        $this->registerParameters($container, $themeConfig['parameters']);
        $this->registerDefinitions($container, $themeConfig['definitions']);
    }

    private function registerParameters($container, array $configParams): void
    {
        // Регистрируем параметры
        foreach ($configParams as $key => $params) {
            if (is_array($params) && $container->has($key)) {
                // Получаем существующий параметр. Container::get() уже разрешает замыкания/фабрики.
                $existingParams = $container->get($key);

                if ($existingParams instanceof Params) {
                    foreach ($params as $k => $value) {
                        $existingParams->set($k, $value);
                    }
                    continue;
                }

                // Убеждаемся, что существующий параметр является массивом перед слиянием
                if (!is_array($existingParams)) {
                    // Если существующий параметр не массив, мы не можем его слить.
                    // В этом случае, новое значение просто перезапишет старое.
                    $container->set($key, fn() => $params);
                    continue;
                }

                // Объединяем существующие параметры с новыми и устанавливаем результат напрямую.
                $mergedParams = array_merge($existingParams, $params);
                $container->set($key, fn() => $mergedParams);

            } else {
                // Если параметра нет или он не массив, устанавливаем его напрямую.
                $container->set($key, fn() => $params);
            }
        }

    }

    private function registerDefinitions($container, array $definitions): void
    {
        // Регистрируем определения сервисов
        foreach ($definitions as $id => $definition) {
            $container->set($id, $definition);
        }
    }
}