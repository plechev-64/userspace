<?php

namespace UserSpace;

use UserSpace\Core\ContainerInterface;

/**
 * Регистрирует сервисы плагина в DI-контейнере.
 */
class ServiceProvider
{
    public function register(ContainerInterface $container): void
    {
        $config = require USERSPACE_PLUGIN_DIR . 'config/container.php';

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