<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Module\Locations\Src\Infrastructure\ItemProvider;
use UserSpace\Common\Module\Settings\App\SettingsEnum;
use UserSpace\Common\Module\Settings\Src\Domain\PluginSettingsInterface;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\Profile\ProfileServiceApiInterface;
use UserSpace\Core\TemplateManagerInterface;

class FrontendManager
{
    public function __construct(
        private readonly ShortcodeManager           $shortcodeManager,
        private readonly TemplateManagerInterface   $templateManager,
        private readonly ItemProvider               $tabProvider,
        private readonly PluginSettingsInterface    $pluginSettings,
        private readonly AssetRegistryInterface     $assetRegistry,
        private readonly HookManagerInterface       $hookManager,
        private readonly ProfileServiceApiInterface $profileService
    )
    {
    }

    public function registerHooks(): void
    {
        $this->hookManager->addAction('init', [$this, 'registerShortcodes']);
        $this->hookManager->addAction('init', [$this, 'prepareAndLoadItems']);
        $this->hookManager->addAction('wp_footer', [$this, 'addModalContainer']);
        $this->hookManager->addAction('wp_footer', [$this, 'renderUserBar']);
    }

    public function registerShortcodes(): void
    {
        $this->shortcodeManager->registerShortcodes();
    }

    public function prepareAndLoadItems(): void
    {
        $this->tabProvider->mergeRegisteredItemsAndConfig();
    }

    public function addModalContainer(): void
    {
        echo $this->templateManager->render('modal_container');
    }

    public function renderUserBar(): void
    {
        if (empty($this->pluginSettings->get(SettingsEnum::ENABLE_USER_BAR))) {
            return;
        }

        $this->assetRegistry->enqueueStyle('usp-user-bar');
        $this->hookManager->addFilter('body_class', function ($classes) {
            $classes[] = 'usp-user-bar-active';
            return $classes;
        });

        $loginPageId = $this->pluginSettings->get(SettingsEnum::LOGIN_PAGE_ID, 0);
        $registrationPageId = $this->pluginSettings->get(SettingsEnum::REGISTRATION_PAGE_ID, 0);

        $login_page_url = $loginPageId ? get_permalink($loginPageId) : wp_login_url();
        $registration_page_url = $registrationPageId ? get_permalink($registrationPageId) : wp_registration_url();
        $account_page_url = $this->profileService->getProfileUrl() ?? home_url();

        echo $this->templateManager->render('user_bar', [
            'login_page_url' => $login_page_url,
            'registration_page_url' => $registration_page_url,
            'account_page_url' => $account_page_url,
        ]);
    }
}