<?php

namespace UserSpace;

use UserSpace\Core\Container\ContainerInterface;
use UserSpace\Core\Container\Params;
use UserSpace\Core\Theme\ThemeManagerInterface;

/**
 * Регистрирует сервисы плагина в DI-контейнере.
 */
class ServiceProvider
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function register(): void
    {
        // 1. Загружаем основной конфиг плагина
        $this->_processConfig(require USERSPACE_PLUGIN_DIR . 'config/container.php');
    }

    /**
     * Обрабатывает конфигурационный массив, регистрируя параметры и определения.
     *
     * @param mixed $config Конфигурация, которая может быть массивом или другим типом.
     */
    private function _processConfig(mixed $config): void
    {
        // Проверяем, что конфигурация является валидным массивом
        if (!is_array($config)) {
            return;
        }

        // Регистрируем параметры, только если они существуют и являются массивом
        if (!empty($config['parameters']) && is_array($config['parameters'])) {
            $this->registerParameters($config['parameters']);
        }

        // Регистрируем определения, только если они существуют и являются массивом
        if (!empty($config['definitions']) && is_array($config['definitions'])) {
            $this->registerDefinitions($config['definitions']);
        }
    }

    public function registerParameters(array $configParams): void
    {
        // Регистрируем параметры
        foreach ($configParams as $key => $params) {
            if (is_array($params) && $this->container->has($key)) {
                // Получаем существующий параметр. Container::get() уже разрешает замыкания/фабрики.
                $existingParams = $this->container->get($key);

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
                    $this->container->set($key, fn() => $params);
                    continue;
                }

                // Объединяем существующие параметры с новыми и устанавливаем результат напрямую.
                $mergedParams = array_merge($existingParams, $params);
                $this->container->set($key, fn() => $mergedParams);

            } else {
                // Если параметра нет или он не массив, устанавливаем его напрямую.
                $this->container->set($key, fn() => $params);
            }
        }

    }

    public function registerDefinitions(array $definitions): void
    {
        // Регистрируем определения сервисов
        foreach ($definitions as $id => $definition) {
            $this->container->set($id, $definition);
        }
    }
}