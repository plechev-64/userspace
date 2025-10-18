<?php

namespace UserSpace\Service;

use UserSpace\TabProvider;

class FrontendManager
{
    private ShortcodeManager $shortcodeManager;
    private TabProvider $tabProvider;

    public function __construct(ShortcodeManager $shortcodeManager, TabProvider $tabProvider)
    {
        $this->shortcodeManager = $shortcodeManager;
        $this->tabProvider = $tabProvider;
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
        echo '
             <div class="usp-modal-backdrop" id="usp-modal-container" role="dialog" aria-modal="true">
                 <div class="usp-modal-content">
                     <div class="usp-modal-header">
                         <button type="button" class="usp-modal-close" aria-label="Close">&times;</button>
                     </div>
                     <div class="usp-modal-body"></div>
                 </div>
             </div>';
    }

    public function renderUserBar(): void
    {
        $settings = get_option('usp_settings', []);
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

        // Подключаем шаблон, передавая в него необходимые переменные.
        include USERSPACE_PLUGIN_DIR . 'views/user-bar-template.php';
    }
}