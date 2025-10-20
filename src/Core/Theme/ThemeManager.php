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
    private string $themesDir;
    private ?string $activeTheme = null;

    public function __construct(
        private readonly ViewedUserContext      $viewedUserContext,
        private readonly OptionManagerInterface $optionManager,
        private readonly UserApiInterface       $userApi
    )
    {
        $this->themesDir = USERSPACE_PLUGIN_DIR . 'themes/';
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
                // Читаем заголовок из файла, как в темах WordPress
                $themeData = get_file_data($indexPath, ['Theme Name' => 'Theme Name']);
                $themeName = !empty($themeData['Theme Name']) ? $themeData['Theme Name'] : ucfirst($dir);
                $themes[$dir] = $themeName;
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
        $this->activeTheme = $settings['account_theme'] ?? 'first'; // 'first' как тема по умолчанию

        $themeEntryPoint = $this->themesDir . $this->activeTheme . '/index.php';

        if (file_exists($themeEntryPoint)) {
            // Подключаем один раз, чтобы зарегистрировать сервисы темы, хуки и т.д.
            include_once $themeEntryPoint;
        }
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

        $themePath = $this->themesDir . $this->activeTheme . '/template.php';

        if (!file_exists($themePath)) {
            return __('Active account theme not found.', 'usp');
        }

        ob_start();
        include $themePath;
        return ob_get_clean();
    }
}