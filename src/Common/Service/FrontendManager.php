<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabProvider;
use UserSpace\Core\OptionManagerInterface;

class FrontendManager
{
    public function __construct(
        private readonly ShortcodeManager $shortcodeManager,
        private readonly TemplateManagerInterface $templateManager,
        private readonly TabProvider $tabProvider,
        private readonly OptionManagerInterface $optionManager
    ) {
    }

    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerShortcodes']);
        add_action('init', [$this, 'registerTabs']);
        add_action('wp_footer', [$this, 'addModalContainer']);
        add_action('wp_footer', [$this, 'renderUserBar']);
    }

    public function registerShortcodes(): void
    {
        $this->shortcodeManager->registerShortcodes();
    }

    public function registerTabs(): void
    {
        $this->tabProvider->registerDefaultTabs();
    }

    public function addModalContainer(): void
    {
        echo $this->templateManager->render('modal_container');
    }

    public function renderUserBar(): void
    {
        $settings = $this->optionManager->get('usp_settings', []);
        if (empty($settings['enable_user_bar'])) {
            return;
        }

        wp_enqueue_style('usp-user-bar');
        add_filter('body_class', function ($classes) {
            $classes[] = 'usp-user-bar-active';
            return $classes;
        });

        $login_page_url = !empty($settings['login_page_id']) ? get_permalink($settings['login_page_id']) : wp_login_url();
        $registration_page_url = !empty($settings['registration_page_id']) ? get_permalink($settings['registration_page_id']) : wp_registration_url();
        $account_page_url = !empty($settings['profile_page_id']) ? get_permalink($settings['profile_page_id']) : home_url();

        echo $this->templateManager->render('user_bar', [
            'login_page_url' => $login_page_url,
            'registration_page_url' => $registration_page_url,
            'account_page_url' => $account_page_url,
        ]);
    }
}