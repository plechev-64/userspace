<?php

namespace UserSpace;

use UserSpace\Core\Tabs\TabConfigManager;
use UserSpace\Core\Tabs\TabContentGenerator;
use UserSpace\Core\Tabs\TabDto;
use UserSpace\Core\Tabs\TabManager;
use UserSpace\Service\ShortcodeManager;

class TabProvider
{
    public function __construct(
        private readonly TabManager $tabManager,
        private readonly TabConfigManager $tabConfigManager,
        private readonly ShortcodeManager $shortcodeManager
    ) {
    }

    public function registerDefaultTabs(): void
    {
        $config = $this->tabConfigManager->load();

        if (null !== $config) {
            // Регистрируем вкладки из сохраненной конфигурации
            foreach ($config as $tabData) {
                $tabDto = new TabDto($tabData['id'], $tabData['title'], $tabData['parentId'] ?? null);
                foreach ($tabData as $key => $value) {
                    if (property_exists($tabDto, $key)) {
                        $tabDto->{$key} = $value;
                    }
                }
                $this->rehydrateContentSource($tabDto);
                $this->tabManager->registerTab($tabDto);
            }
        } else {
            // Если конфигурации нет, регистрируем вкладки по умолчанию
            $this->registerHardcodedDefaults();
        }
    }

    /**
     * Восстанавливает не-сериализуемое свойство contentSource для системных вкладок.
     *
     * @param TabDto $tabDto
     *
     * @return void
     */
    private function rehydrateContentSource(TabDto $tabDto): void
    {
        switch ($tabDto->id) {
            case 'edit_profile':
                $tabDto->contentSource = [ShortcodeManager::class, 'renderProfileFormForAccount'];
                break;
            case 'security':
                $tabDto->contentSource = [TabContentGenerator::class, 'renderSecurityContent'];
                break;
            case 'activity':
                $tabDto->contentSource = [TabContentGenerator::class, 'renderActivityContent'];
                break;
        }
    }

    private function registerHardcodedDefaults(): void
    {
        $profileTab = new TabDto('profile', __('Profile', 'usp'));
        $profileTab->order = 10;
        $profileTab->location = 'sidebar';
        $profileTab->icon = 'dashicons-admin-users';
        $this->tabManager->registerTab($profileTab);

        // Подвкладка "Редактировать профиль" (теперь тоже TabDto)
        $editProfileSubTab = new TabDto('edit_profile', __('Edit Profile', 'usp'), 'profile');
        $editProfileSubTab->location = 'sidebar';
        $editProfileSubTab->contentType = 'rest';
        $editProfileSubTab->contentSource = [$this->shortcodeManager, 'renderProfileFormForAccount'];
        $this->tabManager->registerTab($editProfileSubTab);

        // Вкладка "Безопасность"
        $securityTab = new TabDto('security', __('Security', 'usp'));
        $securityTab->order = 20;
        $securityTab->location = 'sidebar';
        $securityTab->icon = 'dashicons-shield';
        $securityTab->contentType = 'rest';
        $securityTab->contentSource = [TabContentGenerator::class, 'renderSecurityContent'];
        $this->tabManager->registerTab($securityTab);

        // Новая вкладка "Активность" для демонстрации другого местоположения
        $activityTab = new TabDto('activity', __('Activity', 'usp'));
        $activityTab->order = 5;
        $activityTab->location = 'header';
        $activityTab->icon = 'dashicons-update';
        $activityTab->contentType = 'rest';
        $activityTab->contentSource = [TabContentGenerator::class, 'renderActivityContent']; // phpcs:ignore
        $this->tabManager->registerTab($activityTab);
    }
}