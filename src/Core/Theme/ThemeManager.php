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
    /** @var array<string, ThemeInterface> */
    private array $themes = [];

    public function __construct(
        private readonly \UserSpace\Core\ContainerInterface $container,
        private readonly ViewedUserContext      $viewedUserContext,
        private readonly OptionManagerInterface $optionManager,
        private readonly UserApiInterface       $userApi
    )
    {
        $this->themesDir = USERSPACE_PLUGIN_DIR . self::THEMES_DIR_NAME . '/';
    }

    private ?ThemeInterface $activeTheme = null;
    
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

        // Сохраняем тему в кэш по её системному имени (имени папки)
        $dirName = basename($themeObject->getPath());
        $this->themes[$dirName] = $themeObject;

        return $themeObject;
    }

    /**
     * Сканирует директорию и возвращает список доступных тем.
     *
     * @return array<string, string> Ассоциативный массив [dir_name => theme_name].
     */
    public function discoverThemes(): array
    {
        if (!is_dir($this->themesDir)) {
            return [];
        }

        $dirs = array_filter(scandir($this->themesDir), fn($item) => is_dir($this->themesDir . $item) && !in_array($item, ['.', '..']));

        foreach ($dirs as $dir) {
            $indexPath = $this->themesDir . $dir . '/index.php';
            if (file_exists($indexPath)) {
                // Если тема уже загружена, просто берем ее из кэша
                if (isset($this->themes[$dir])) {
                    continue;
                }

                // Передаем себя в качестве регистратора
                $themeRegistry = $this;
                // Используем require_once, чтобы избежать повторного объявления классов.
                // Результат выполнения файла (объект темы) будет сохранен в $this->themes
                // через вызов метода register() из файла index.php темы.
                require_once $indexPath;
            }
        }

        // Теперь, когда все темы загружены в $this->themes, формируем массив для ответа.
        $discoveredThemes = [];
        foreach ($this->themes as $dirName => $themeObject) {
            $discoveredThemes[$dirName] = $themeObject->getName();
        }

        return $discoveredThemes;
    }

    /**
     * Загружает точку входа активной темы для регистрации её сервисов и хуков.
     * Должен вызываться на раннем этапе, например, 'plugins_loaded'.
     */
    public function loadActiveTheme(): void
    {
        $this->discoverThemes(); // Убедимся, что все темы обнаружены и закэшированы

        $settings = $this->optionManager->get('usp_settings', []);
        $activeThemeDir = $settings['account_theme'] ?? 'first'; // 'first' как тема по умолчанию

        if (isset($this->themes[$activeThemeDir])) {
            $this->activeTheme = $this->themes[$activeThemeDir];
            $this->activeTheme->setup($this->container);
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