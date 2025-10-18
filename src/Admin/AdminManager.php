<?php

namespace UserSpace\Admin;

use UserSpace\Admin\Page\ProfileFormPage;
use UserSpace\Admin\Page\RegistrationFormPage;
use UserSpace\Admin\Page\SettingsPage;
use UserSpace\Admin\Page\TabsConfigPage;
use UserSpace\Admin\Page\UserCardListPage;
use UserSpace\Admin\Page\UserTableListPage;

class AdminManager
{
    public function __construct(
        private readonly SettingsPage         $settingsPage,
        private readonly ProfileFormPage      $profileFormPage,
        private readonly RegistrationFormPage $registrationFormPage,
        private readonly TabsConfigPage       $tabsConfigPage,
        private readonly AdminProfileFields   $adminProfileFields,
        private readonly UserCardListPage     $userListPage,
        private readonly UserTableListPage    $userListTablePage
    )
    {
    }

    public function registerHooks(): void
    {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', [$this, 'registerAdminPages']);
        add_action('admin_init', [$this, 'registerPluginSettings']);
        add_action('admin_init', [$this, 'registerAdminProfileFields']);
    }

    public function registerAdminPages(): void
    {
        $this->settingsPage->register();
        $this->profileFormPage->register();
        $this->registrationFormPage->register();
        $this->tabsConfigPage->register();
        $this->userListPage->register();
        $this->userListTablePage->register();
    }

    public function registerPluginSettings(): void
    {
        $this->settingsPage->registerSettings();
    }

    public function registerAdminProfileFields(): void
    {
        $this->adminProfileFields->registerHooks();
    }
}