<?php

namespace UserSpace\Service;

use UserSpace\Admin\Page\ProfileFormPage;
use UserSpace\Admin\Page\RegistrationFormPage;
use UserSpace\Admin\Page\SettingsPage;
use UserSpace\Admin\Page\TabsConfigPage;
use UserSpace\Admin\Page\UserCardListPage;
use UserSpace\Admin\Page\UserTableListPage;

class AssetsManager
{
    public function __construct(
        protected readonly SettingsPage         $settingsPage,
        protected readonly ProfileFormPage      $profileFormPage,
        protected readonly RegistrationFormPage $registrationFormPage,
        protected readonly TabsConfigPage       $tabsConfigPage,
    )
    {
    }

    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerAllAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueuePublicAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Регистрирует все ассеты плагина (стили и скрипты).
     */
    public function registerAllAssets(): void
    {
        wp_register_style('usp-form', USERSPACE_PLUGIN_URL . 'assets/css/form.css', [], USERSPACE_VERSION);
        wp_register_script('usp-core', USERSPACE_PLUGIN_URL . 'assets/js/core.js', [], USERSPACE_VERSION, true);
        wp_register_style('usp-modal', USERSPACE_PLUGIN_URL . 'assets/css/modal.css', [], USERSPACE_VERSION);
        wp_register_script('usp-form-handler', USERSPACE_PLUGIN_URL . 'assets/js/form-handler.js', ['usp-core'], USERSPACE_VERSION, true);
        wp_register_script('usp-login-handler', USERSPACE_PLUGIN_URL . 'assets/js/login-handler.js', ['usp-core'], USERSPACE_VERSION, true);
        wp_register_script('usp-registration-handler', USERSPACE_PLUGIN_URL . 'assets/js/registration-handler.js', ['usp-core'], USERSPACE_VERSION, true);
        wp_register_script('usp-forgot-password-handler', USERSPACE_PLUGIN_URL . 'assets/js/forgot-password-handler.js', ['usp-core'], USERSPACE_VERSION, true);
        wp_register_style('usp-user-bar', USERSPACE_PLUGIN_URL . 'assets/css/user-bar.css', [], USERSPACE_VERSION);
        wp_register_script('usp-uploader-handler', USERSPACE_PLUGIN_URL . 'assets/js/uploader-handler.js', ['usp-core'], USERSPACE_VERSION, true);
    }

    /**
     * Подключает публичные ассеты.
     */
    public function enqueuePublicAssets(): void
    {
        wp_enqueue_script('usp-uploader-handler');
        wp_enqueue_style('usp-modal');

        // Подключаем core.js и локализуем его
        wp_enqueue_script('usp-core');
        $this->localizeCoreScript();
    }

    /**
     * Подключает ассеты для админ-панели.
     * @param string $hook
     */
    public function enqueueAdminAssets(string $hook): void
    {
        // Подключаем core.js и локализуем его
        wp_enqueue_script('usp-core');
        $this->localizeCoreScript();

        // Подключаем ассеты для конкретных страниц
        $this->profileFormPage->enqueueAssets($hook);
        $this->registrationFormPage->enqueueAssets($hook);
        $this->settingsPage->enqueueAssets($hook);
        $this->tabsConfigPage->enqueueAssets($hook);
    }

    /**
     * Локализует core-скрипт, передавая в него настройки API.
     */
    private function localizeCoreScript(): void
    {
        wp_localize_script(
            'usp-core',
            'uspApiSettings',
            [
                'root' => esc_url_raw(rest_url()),
                'namespace' => 'userspace/v1',
                'nonce' => wp_create_nonce('wp_rest'),
            ]
        );

        // Глобальный объект для локализации JS
        wp_localize_script(
            'usp-core',
            'uspL10n',
            [
                'uploader' => [
                    'validating' => __('Validating...', 'usp'),
                    'uploading' => __('Uploading...', 'usp'),
                    'success' => __('Success!', 'usp'),
                    'error' => __('Error: {message}', 'usp'),
                    'fileTooLarge' => __('File is too large. Maximum size is {maxSize} MB.', 'usp'),
                    'invalidFileType' => __('Invalid file type.', 'usp'),
                    'imageTooSmall' => __('Image is too small. Minimum dimensions are {minWidth}x{minHeight}px.', 'usp'),
                    'imageTooLarge' => __('Image is too large. Maximum dimensions are {maxWidth}x{maxHeight}px.', 'usp'),
                    'imageReadError' => __('Could not read image dimensions.', 'usp'),
                    'remove' => __('Remove', 'usp'),
                    'previewAlt' => __('Preview', 'usp'),
                ],
                'login' => [
                    'loggingIn' => __('Logging in...', 'usp'),
                ],
                'registration' => [
                    'registering' => __('Registering...', 'usp'),
                ],
            ]
        );
    }
}