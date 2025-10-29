<?php

namespace UserSpace;

use UserSpace\Admin\AdminManager;
use UserSpace\Common\Module\Form\Src\Infrastructure\DefaultFormConfigs;
use UserSpace\Common\Module\Locations\App\Default\ActivityTab;
use UserSpace\Common\Module\Locations\App\Default\ClearCacheButton;
use UserSpace\Common\Module\Locations\App\Default\EditProfileTab;
use UserSpace\Common\Module\Locations\App\Default\ProfileTab;
use UserSpace\Common\Module\Locations\App\Default\SecurityTab;
use UserSpace\Common\Module\Locations\App\Default\UserListTab;
use UserSpace\Common\Module\Locations\Src\Domain\ItemRegistryInterface;
use UserSpace\Common\Module\SetupWizard\Infrastructure\DefaultSetupWizardConfig;
use UserSpace\Common\Service\AssetsManager;
use UserSpace\Common\Service\AvatarManager;
use UserSpace\Common\Service\CronManager;
use UserSpace\Common\Service\FrontendManager;
use UserSpace\Core\Addon\AddonManagerInterface;
use UserSpace\Core\Addon\Theme\ThemeManagerInterface;
use UserSpace\Core\Container\Container;
use UserSpace\Core\Container\ContainerInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\Localization\LocalizationApiInterface;
use UserSpace\Core\Rest\InitWpRest;

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
        private readonly LocalizationApiInterface $localizationApi,
        private readonly ItemRegistryInterface    $itemRegistry
    )
    {
        $this->initHooks();
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
            (new ServiceProvider($container))->register();

            $hooManager = $container->get(HookManagerInterface::class);

            // 2. Затем создаем экземпляр Plugin, передавая ему уже готовые зависимости из контейнера
            self::$instance = new self(
                $container,
                $container->get(HookManagerInterface::class),
                $container->get(LocalizationApiInterface::class),
                $container->get(ItemRegistryInterface::class)
            );

            $hooManager->doAction('userspace_loaded', $container->get(AddonManagerInterface::class));
            $hooManager->doAction('userspace_addons_init');
        }

        return self::$instance;
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
        $this->localizationApi->loadPluginTextdomain('usp', dirname(plugin_basename(USERSPACE_PLUGIN_FILE)) . '/languages'
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
     * Инициализация хуков WordPress.
     */
    private function initHooks(): void
    {
        $this->initRestApi();

        $this->hookManager->addAction('plugins_loaded', [$this, 'run']);
        $this->hookManager->addAction('init', [$this, 'loadTextdomain']);
        $this->hookManager->addAction('userspace_addons_init', [$this, 'addonsInit'], 10);
        $this->hookManager->addAction('userspace_addons_init', [$this, 'themeInit'], 15);
        $this->hookManager->addAction('userspace_addons_init', [$this, 'registerDefaultItems'], 20);
        $this->hookManager->addAction('userspace_addons_init', [$this, 'registerDefaultConfigs'], 25);

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

    public function themeInit(): void
    {
        $manager = $this->container->get(ThemeManagerInterface::class);
        $manager->initialize();
    }

    public function addonsInit(): void
    {
        $manager = $this->container->get(AddonManagerInterface::class);
        $manager->initialize();
    }

    /**
     * Регистрирует дефолтные вкладки и кнопки плагина.
     *
     * @return void
     */
    public function registerDefaultItems(): void
    {
        $this->itemRegistry->registerItem(ProfileTab::class);
        $this->itemRegistry->registerItem(EditProfileTab::class);
        $this->itemRegistry->registerItem(SecurityTab::class);
        $this->itemRegistry->registerItem(ActivityTab::class);
        $this->itemRegistry->registerItem(UserListTab::class);
        $this->itemRegistry->registerItem(ClearCacheButton::class);
    }

    /**
     * Регистрирует конфигурации по умолчанию.
     */
    public function registerDefaultConfigs(): void
    {
        $setupWizardConfig = $this->container->get(DefaultSetupWizardConfig::class);
        $setupWizardConfig->register();

        $defaultFormConfigs = $this->container->get(DefaultFormConfigs::class);
        $defaultFormConfigs->register();
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