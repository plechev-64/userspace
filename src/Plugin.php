<?php

namespace UserSpace;

use UserSpace\Admin\AdminManager;
use UserSpace\Core\Container;
use UserSpace\Core\ContainerInterface;
use UserSpace\Core\Cron\CronManager;
use UserSpace\Core\Queue\QueueManager;
use UserSpace\Core\InitWpRest;
use UserSpace\Core\Theme\ThemeManager;
use UserSpace\Service\AssetsManager;
use UserSpace\Service\FrontendManager;

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
    private static ?Plugin $instance = null;

    /**
     * @var ContainerInterface Контейнер зависимостей.
     */
    private readonly ContainerInterface $container;

    /**
     * Конструктор класса.
     * Приватный, чтобы предотвратить создание новых экземпляров.
     */
    private function __construct()
    {
        $this->container = new Container();
        (new ServiceProvider())->register($this->container);
        $this->initHooks();
    }

    /**
     * Инициализация хуков WordPress.
     */
    private function initHooks(): void
    {
        $this->initRestApi();

        // Хук для запуска основной логики плагина
        add_action('plugins_loaded', [$this, 'run']);
        add_action('plugins_loaded', [$this, 'loadTheme']);

        // Хук для загрузки перевода
        add_action('init', [$this, 'loadTextdomain']);

        // Инициализация менеджеров
        $this->container->get(AssetsManager::class)->registerHooks();
        $this->container->get(AdminManager::class)->registerHooks();
        $this->container->get(FrontendManager::class)->registerHooks();
        $this->container->get(CronManager::class)->registerHooks();

        // Хук для подмены стандартных аватаров
        $avatarManager = $this->container->get(Service\AvatarManager::class);
        add_filter('pre_get_avatar_data', [$avatarManager, 'replaceAvatarData'], 20, 2);
    }

    /**
     * Загружает активную тему для регистрации её сервисов.
     */
    public function loadTheme(): void
    {
        $themeManager = $this->container->get(ThemeManager::class);
        $themeManager->loadActiveTheme();
    }

    /**
     * Инициализирует и регистрирует REST роуты.
     */
    public function initRestApi(): void
    {
        /** @var InitWpRest $restInit */
        $restInit = $this->container->get(InitWpRest::class);
        $restInit->__invoke();
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
        load_plugin_textdomain(
            'usp',
            false,
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
    public static function getInstance(): Plugin
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
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