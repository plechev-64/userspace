<?php

namespace UserSpace\Core\Theme;

use UserSpace\Common\Service\ViewedUserContext;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\User\UserApiInterface;

/**
 * Управляет темами личного кабинета.
 */
class ThemeManager
{
    private const THEMES_DIR_NAME = 'themes';

    private string $themesDir;
    private ?ThemeInterface $activeTheme = null;
    private ?string $discoveredThemeName = null;

    public function __construct(
        private readonly \UserSpace\Core\ContainerInterface $container,
        private readonly ViewedUserContext      $viewedUserContext,
        private readonly OptionManagerInterface $optionManager,
        private readonly UserApiInterface       $userApi
    )
    {
        $this->themesDir = USERSPACE_PLUGIN_DIR . self::THEMES_DIR_NAME . '/';
    }
    
    /**
     * Регистрирует класс темы.
     * Этот метод вызывается из файла index.php темы.
     *
     * @param string $themeClassName Полное имя класса темы.
     * @return ThemeInterface|null
     */
    public function register(string $themeClassName): ?ThemeInterface
    {
        if (!class_exists($themeClassName) || !is_subclass_of($themeClassName, ThemeInterface::class)) {
            // В реальном приложении здесь можно логировать ошибку
            return null;
        }
        
        $themeObject = new $themeClassName();
        
        $this->discoveredThemeName = $themeObject->getName();
        return $themeObject;
    }

    /**
     * Сканирует директорию и возвращает список доступных тем.
     *
     * @return array<string, string>
     */
    public function discoverThemes(): array
    {
        $themes = [];
        if (!is_dir($this->themesDir)) {
            return [];
        }

        $dirs = array_filter(scandir($this->themesDir), fn($item) => is_dir($this->themesDir . $item) && !in_array($item, ['.', '..']));

        foreach ($dirs as $dir) {
            $indexPath = $this->themesDir . $dir . '/index.php';
            if (file_exists($indexPath)) {
                // Передаем себя в качестве регистратора
                $themeRegistry = $this;
                $themeObject = require $indexPath;
                
                if ($themeObject instanceof ThemeInterface) {
                    $themes[$dir] = $themeObject->getName();
                }
            }
        }

        return $themes;
    }

    /**
     * Загружает точку входа активной темы для регистрации её сервисов и хуков.
     * Должен вызываться на раннем этапе, например, 'plugins_loaded'.
     */
    public function loadActiveTheme(): void
    {
        $settings = $this->optionManager->get('usp_settings', []);
        $activeThemeDir = $settings['account_theme'] ?? 'first'; // 'first' как тема по умолчанию

        $themeEntryPoint = $this->themesDir . $activeThemeDir . '/index.php';

        if (file_exists($themeEntryPoint)) {
            // Передаем себя в качестве регистратора в область видимости файла
            $themeRegistry = $this;
            $themeObject = require_once $themeEntryPoint;

            if ($themeObject instanceof ThemeInterface) {
                $this->activeTheme = $themeObject;
                $this->activeTheme->setup($this->container);
            }
        }
    }

    /**
     * Загружает конфигурационный файл активной темы, если он существует.
     *
     * @return array Конфигурация темы или пустой массив.
     */
    public function loadActiveThemeConfig(): array
    {
        if ($this->activeTheme && ($configPath = $this->activeTheme->getContainerConfigPath())) {
            if (file_exists($configPath)) {
                return require $configPath;
            }
        }


        return [];
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
}