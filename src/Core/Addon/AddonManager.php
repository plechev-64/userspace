<?php

namespace UserSpace\Core\Addon;

use UserSpace\Core\Container\ContainerInterface;
use UserSpace\Core\Theme\ThemeInterface;
use UserSpace\Core\Theme\ThemeManager;
use UserSpace\ServiceProvider;

if ( ! defined('ABSPATH')) {
    exit;
}

class AddonManager implements AddonManagerInterface
{
    /** @var AddonInterface[] */
    private array $addons = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ServiceProvider $serviceProvider,
        private readonly ThemeManager $themeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function register(string $addonClassName): void
    {
        if ( ! class_exists($addonClassName) || ! is_subclass_of($addonClassName, AddonInterface::class)) {
            // Можно добавить логирование ошибки
            return;
        }

        if(is_subclass_of($addonClassName, ThemeInterface::class)){
            $this->themeManager->register($addonClassName);
            return;
        }

        $this->addons[] = new $addonClassName();

    }

    /**
     * Инициализирует все зарегистрированные дополнения.
     */
    public function initializeAddons(): void
    {
        foreach ($this->addons as $addon) {
            // Загружаем определения из DI контейнера аддона
            $configPath = $addon->getContainerConfigPath();
            if ($configPath && file_exists($configPath)) {
                $addonConfig = require $configPath;

                if ( ! empty($addonConfig['parameters']) && is_array($addonConfig['parameters'])) {
                    $this->serviceProvider->registerParameters($addonConfig['parameters']);
                }

                if ( ! empty($addonConfig['definitions']) && is_array($addonConfig['definitions'])) {
                    $this->serviceProvider->registerDefinitions($addonConfig['definitions']);
                }
            }

            // Выполняем основную логику инициализации аддона
            $addon->setup($this->container);
        }
    }
}