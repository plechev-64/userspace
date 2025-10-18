<?php

namespace UserSpace;

use UserSpace\Core\Tabs\TabConfigManager;
use UserSpace\Core\Tabs\TabManager;
use UserSpace\Tabs\ActivityTab;
use UserSpace\Tabs\EditProfileTab;
use UserSpace\Tabs\ProfileTab;
use UserSpace\Tabs\SecurityTab;

class TabProvider
{
    public function __construct(
        private readonly TabManager $tabManager,
        private readonly TabConfigManager $tabConfigManager
    ) {
    }

    public function registerDefaultTabs(): void
    {
        $config = $this->tabConfigManager->load();

        if (null !== $config) {
            // Регистрируем вкладки из сохраненной конфигурации
            foreach ($config as $tabData) {
                if (isset($tabData['class'])) {
                    // Регистрируем класс и сразу передаем данные для обновления состояния
                    $this->tabManager->registerTab($tabData['class'], $tabData);
                }
            }
        } else {
            // Если конфигурации нет, регистрируем вкладки по умолчанию
            $this->registerHardcodedDefaults();

            // ... и сразу создаем для них файл конфигурации
            $registeredTabs = $this->tabManager->getAllRegisteredTabs(true); // Получаем плоский список

            $defaultConfig = [];
            foreach ($registeredTabs as $tab) {
                $tabData = $tab->toArray();
                $tabData['class'] = get_class($tab); // Добавляем имя класса
                $defaultConfig[] = $tabData;
            }

            $this->tabConfigManager->save($defaultConfig);
        }
    }

    private function registerHardcodedDefaults(): void
    {
        $tabClasses = [
            ProfileTab::class,
            EditProfileTab::class,
            SecurityTab::class,
            ActivityTab::class,
        ];

        foreach ($tabClasses as $tabClass) {
            $this->tabManager->registerTab($tabClass);
        }
    }
}