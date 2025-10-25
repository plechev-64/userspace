<?php

namespace UserSpace\Core\Theme;

use UserSpace\Common\Module\Settings\App\SettingsEnum;
use UserSpace\Common\Module\Settings\Src\Domain\PluginSettingsInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Common\Service\ViewedUserContext;
use UserSpace\Core\Container\ContainerInterface;
use UserSpace\ServiceProvider;

/**
 * Управляет темами личного кабинета.
 */
class ThemeManager implements ThemeManagerInterface
{
    private const THEMES_DIR_NAME = 'themes';
    private const DEFAULT_THEME_SLUG = 'first';
    private ?ThemeInterface $activeTheme = null;
    /** @var array<string, ThemeInterface> */
    private array $themes = [];
    private string $localThemesDir;

    public function __construct(
        private readonly ContainerInterface      $container,
        private readonly ViewedUserContext       $viewedUserContext,
        private readonly PluginSettingsInterface $optionManager,
        private readonly UserApiInterface        $userApi,
        private readonly ServiceProvider         $serviceProvider
    )
    {
        $this->localThemesDir = USERSPACE_PLUGIN_DIR . self::THEMES_DIR_NAME . '/';
        $this->includeLocalThemes();
    }

    /**
     * Регистрирует тему, которая является дополнением.
     * Этот метод вызывается из AddonManager.
     *
     * @param string $themeClassName Полное имя класса темы.
     */
    public function register(string $themeClassName): void
    {
        if (!class_exists($themeClassName) || !is_subclass_of($themeClassName, ThemeInterface::class)) {
            return;
        }

        /** @var ThemeInterface $theme */
        $theme = new $themeClassName();

        if (isset($this->themes[$theme->getSlug()])) {
            return;
        }

        $this->themes[$theme->getSlug()] = $theme;
    }

    /**
     * Получает список доступных тем от менеджера дополнений.
     *
     * @return array<string, string> Ассоциативный массив [slug => theme_name].
     */
    public function discoverThemes(): array
    {
        return array_map(function ($themeObject) {
            return $themeObject->getName();
        }, $this->themes);
    }

    /**
     * Загружает точку входа активной темы для регистрации её сервисов и хуков.
     */
    public function loadActiveTheme(): void
    {
        $activeThemeSlug = $this->optionManager->get(SettingsEnum::ACCOUNT_THEME, self::DEFAULT_THEME_SLUG);
        if (isset($this->themes[$activeThemeSlug])) {
            $this->activeTheme = $this->themes[$activeThemeSlug];
            $configPath = $this->activeTheme->getContainerConfigPath();

            if ($configPath && file_exists($configPath)) {
                $addonConfig = require $configPath;

                if (!empty($addonConfig['parameters']) && is_array($addonConfig['parameters'])) {
                    $this->serviceProvider->registerParameters($addonConfig['parameters']);
                }

                if (!empty($addonConfig['definitions']) && is_array($addonConfig['definitions'])) {
                    $this->serviceProvider->registerDefinitions($addonConfig['definitions']);
                }
            }

            $this->activeTheme->setup($this->container);
        }
    }

    public function getActiveTheme(): ?ThemeInterface
    {
        return $this->activeTheme;
    }

    /**
     * Рендерит активную тему личного кабинета.
     *
     * @return string
     */
    public function renderActiveTheme(): string
    {
        // Если пользователь не авторизован и не пытается посмотреть чужой профиль, показываем форму входа.
        if (!$this->userApi->isUserLoggedIn() && !$this->viewedUserContext->isProfileRequestedViaQueryVar()) {
            return do_shortcode('[usp_login_form]');
        }

        if (null === $this->activeTheme) {
            return __('Active theme is not loaded. Please check plugin initialization.', 'usp');
        }

        $themePath = $this->activeTheme->getTemplatePath();

        if (!file_exists($themePath)) {
            return __('Active account theme not found.', 'usp');
        }

        // Готовим данные для передачи в шаблон
        $data = [];
        if (method_exists($this->activeTheme, 'prepareTemplateData')) {
            $data = $this->activeTheme->prepareTemplateData();
        }

        ob_start();
        // Импортируем переменные в локальную область видимости шаблона
        extract($data, EXTR_SKIP);
        include $themePath;
        return ob_get_clean();
    }

    private function includeLocalThemes(): void
    {
        if (is_dir($this->localThemesDir)) {
            $dirs = array_filter(scandir($this->localThemesDir), fn($item) => is_dir($this->localThemesDir . $item) && !in_array($item, ['.', '..']));
            foreach ($dirs as $dir) {
                $indexPath = $this->localThemesDir . $dir . '/index.php';
                if (file_exists($indexPath)) {
                    require_once $indexPath;
                }
            }
        }
    }
}