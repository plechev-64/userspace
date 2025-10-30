<?php

namespace UserSpace\Common\Service;

use UserSpace\Admin\Page\ProfileFormPage;
use UserSpace\Admin\Page\RegistrationFormPage;
use UserSpace\Admin\Page\SettingsPage;
use UserSpace\Admin\Page\SetupWizardPage;
use UserSpace\Admin\Page\TabsConfigPage;
use UserSpace\Common\Module\Settings\App\SettingsEnum;
use UserSpace\Common\Module\Settings\Src\Domain\PluginSettingsInterface;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;

class AssetsManager
{
    public function __construct(
        protected readonly SettingsPage            $settingsPage,
        protected readonly ProfileFormPage         $profileFormPage,
        protected readonly RegistrationFormPage    $registrationFormPage,
        protected readonly TabsConfigPage          $tabsConfigPage,
        protected readonly SetupWizardPage         $setupWizardPage,
        protected readonly StringFilterInterface   $str,
        protected readonly AssetRegistryInterface  $assetRegistry,
        protected readonly HookManagerInterface    $hookManager,
        protected readonly PluginSettingsInterface $pluginSettings
    )
    {
    }

    public function registerHooks(): void
    {
        $this->hookManager->addAction('init', [$this, 'registerAllAssets']);
        $this->hookManager->addAction('wp_enqueue_scripts', [$this, 'enqueuePublicAssets']);
        $this->hookManager->addAction('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Регистрирует все ассеты плагина (стили и скрипты).
     */
    public function registerAllAssets(): void
    {
        $this->assetRegistry->registerStyle('usp-icons',USERSPACE_PLUGIN_URL . 'assets/icons/usp-awesome.css', [], USERSPACE_VERSION);
        $this->assetRegistry->registerScript('usp-iconpicker', USERSPACE_PLUGIN_URL . 'assets/icons/iconpicker/iconpicker.js', [], USERSPACE_VERSION);
        $this->assetRegistry->registerStyle('usp-iconpicker',USERSPACE_PLUGIN_URL . 'assets/icons/iconpicker/iconpicker.css', [], USERSPACE_VERSION);
        $this->assetRegistry->registerStyle('usp-main', USERSPACE_PLUGIN_URL . 'assets/css/main.css', [], USERSPACE_VERSION);
        $this->assetRegistry->registerStyle('usp-form', USERSPACE_PLUGIN_URL . 'assets/css/form.css', [], USERSPACE_VERSION);
        $this->assetRegistry->registerScript('usp-core', USERSPACE_PLUGIN_URL . 'assets/js/core.js', [], USERSPACE_VERSION, true);
        $this->assetRegistry->registerStyle('usp-modal', USERSPACE_PLUGIN_URL . 'assets/css/modal.css', [], USERSPACE_VERSION);
        $this->assetRegistry->registerScript('usp-form-handler', USERSPACE_PLUGIN_URL . 'assets/js/form-handler.js', ['usp-core'], USERSPACE_VERSION, true);
        $this->assetRegistry->registerScript('usp-login-handler', USERSPACE_PLUGIN_URL . 'assets/js/login-handler.js', ['usp-core'], USERSPACE_VERSION, true);
        $this->assetRegistry->registerScript('usp-registration-handler', USERSPACE_PLUGIN_URL . 'assets/js/registration-handler.js', ['usp-core'], USERSPACE_VERSION, true);
        $this->assetRegistry->registerScript('usp-forgot-password-handler', USERSPACE_PLUGIN_URL . 'assets/js/forgot-password-handler.js', ['usp-core'], USERSPACE_VERSION, true);
        $this->assetRegistry->registerStyle('usp-user-bar', USERSPACE_PLUGIN_URL . 'assets/css/user-bar.css', [], USERSPACE_VERSION);
        $this->assetRegistry->registerScript('usp-uploader-handler', USERSPACE_PLUGIN_URL . 'assets/js/uploader-handler.js', ['usp-core'], USERSPACE_VERSION, true);
    }

    /**
     * Подключает публичные ассеты.
     */
    public function enqueuePublicAssets(): void
    {
        $this->assetRegistry->enqueueStyle('usp-main');
        $this->assetRegistry->enqueueStyle('usp-icons');

        $this->assetRegistry->enqueueScript('usp-uploader-handler');
        $this->assetRegistry->enqueueStyle('usp-modal');

        // Подключаем core.js и локализуем его
        $this->assetRegistry->enqueueScript('usp-core');
        $this->localizeCoreScript();
    }

    /**
     * Подключает ассеты для админ-панели.
     * @param string $hook
     */
    public function enqueueAdminAssets(string $hook): void
    {
        // Подключаем core.js и локализуем его
        $this->assetRegistry->enqueueScript('usp-core');
        $this->localizeCoreScript();

        // Подключаем ассеты для конкретных страниц
        $this->profileFormPage->enqueueAssets($hook);
        $this->registrationFormPage->enqueueAssets($hook);
        $this->settingsPage->enqueueAssets($hook);
        $this->tabsConfigPage->enqueueAssets($hook);
        $this->setupWizardPage->enqueueAssets($hook);
    }

    /**
     * Локализует core-скрипт, передавая в него настройки API.
     */
    private function localizeCoreScript(): void
    {
        $this->assetRegistry->localizeScript(
            'usp-core',
            'uspApiSettings',
            [
                'root' => $this->str->escUrlRaw(rest_url()),
                'namespace' => USERSPACE_REST_NAMESPACE,
                'nonce' => wp_create_nonce('wp_rest'),
            ]
        );

        $this->assetRegistry->localizeScript(
            'usp-core',
            'uspCoreParams',
            [
                'tabParam' => $this->pluginSettings->get(SettingsEnum::PROFILE_TAB_QUERY_VAR, 'tab'),
            ]
        );

        // Глобальный объект для локализации JS
        $this->assetRegistry->localizeScript(
            'usp-core',
            'uspL10n',
            [
                'uploader' => [
                    'validating' => $this->str->translate('Validating...'),
                    'uploading' => $this->str->translate('Uploading...'),
                    'success' => $this->str->translate('Success!'),
                    'error' => $this->str->translate('Error: {message}'),
                    'fileTooLarge' => $this->str->translate('File is too large. Maximum size is {maxSize} MB.'),
                    'invalidFileType' => $this->str->translate('Invalid file type.'),
                    'imageTooSmall' => $this->str->translate('Image is too small. Minimum dimensions are {minWidth}x{minHeight}px.'),
                    'imageTooLarge' => $this->str->translate('Image is too large. Maximum dimensions are {maxWidth}x{maxHeight}px.'),
                    'imageReadError' => $this->str->translate('Could not read image dimensions.'),
                    'remove' => $this->str->translate('Remove'),
                    'previewAlt' => $this->str->translate('Preview'),
                ],
                'login' => [
                    'loggingIn' => $this->str->translate('Logging in...'),
                ],
                'registration' => [
                    'registering' => $this->str->translate('Registering...'),
                ],
            ]
        );
    }
}