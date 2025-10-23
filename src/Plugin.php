<?php

namespace UserSpace;

use UserSpace\Admin\AdminManager;
use UserSpace\Common\Service\AssetsManager;
use UserSpace\Common\Service\AvatarManager;
use UserSpace\Common\Service\CronManager;
use UserSpace\Common\Service\FrontendManager;
use UserSpace\Core\Container\Container;
use UserSpace\Core\Container\ContainerInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\Localization\LocalizationApiInterface;
use UserSpace\Core\Rest\InitWpRest;
use UserSpace\Core\Theme\ThemeManagerInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Основной класс плагина UserSpace.
 *
 * Используется паттерн Singleton для гарантии единственного экземпляра.
 */
final class Plugin
{

    /**
     * @var Plugin|null Экземпляр класса.
     */
    private static ?self $instance = null;

    /**
     * Конструктор класса.
     * Приватный, чтобы предотвратить создание новых экземпляров. Принимает уже сконфигурированные зависимости.
     *
     * @param ContainerInterface $container
     * @param HookManagerInterface $hookManager
     * @param LocalizationApiInterface $localizationApi
     */
    private function __construct(
        private readonly ContainerInterface       $container,
        private readonly HookManagerInterface     $hookManager,
        private readonly LocalizationApiInterface $localizationApi
    )
    {
        $this->initHooks();
    }

    /**
     * Загружает активную тему для регистрации её сервисов.
     */
    public function loadTheme(): void
    {
        $themeManager = $this->container->get(ThemeManagerInterface::class);
        $themeManager->loadActiveTheme();
    }

    /**
     * Основная логика запуска плагина после загрузки всех плагинов.
     */
    public function run(): void
    {
        // Здесь можно добавлять другие хуки (actions и filters)
        // Например: add_action( 'init', [ $this, 'register_post_types' ] );
    }

    /**
     * Загрузка файла перевода (.mo) для локализации плагина.
     */
    public function loadTextdomain(): void
    {
        $this->localizationApi->loadPluginTextdomain('usp',
            dirname(plugin_basename(USERSPACE_PLUGIN_FILE)) . '/languages'
        );
    }

    /**
     * Возвращает контейнер зависимостей.
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Получение единственного экземпляра класса.
     *
     * @return Plugin
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            $container = new Container();
            // 1. Сначала конфигурируем контейнер
            (new ServiceProvider())->register($container);

            // 2. Затем создаем экземпляр Plugin, передавая ему уже готовые зависимости из контейнера
            self::$instance = new self(
                $container,
                $container->get(HookManagerInterface::class),
                $container->get(LocalizationApiInterface::class)
            );
        }

        return self::$instance;
    }

    /**
     * Инициализация хуков WordPress.
     */
    private function initHooks(): void
    {
        $this->initRestApi();

        $this->hookManager->addAction('plugins_loaded', [$this, 'run']);
        /** @todo возможно удалить тк решил загружать активную тему через ServiceProvider */
        //$this->hookManager->addAction('plugins_loaded', [$this, 'loadTheme']);
        $this->hookManager->addAction('init', [$this, 'loadTextdomain']);

        // Инициализация менеджеров
        $this->container->get(AssetsManager::class)->registerHooks();
        $this->container->get(AdminManager::class)->registerHooks();
        $this->container->get(FrontendManager::class)->registerHooks();
        $this->container->get(CronManager::class)->registerHooks();
        $this->container->get(AvatarManager::class)->registerHooks();
    }

    /**
     * Инициализирует и регистрирует REST роуты.
     */
    private function initRestApi(): void
    {
        /** @var InitWpRest $restInit */
        $restInit = $this->container->get(InitWpRest::class);
        $restInit->__invoke();
    }

    /**
     * Запрещаем клонирование для Singleton.
     */
    private function __clone()
    {
    }

    /**
     * Запрещаем десериализацию для Singleton.
     */
    public function __wakeup()
    {
    }
}