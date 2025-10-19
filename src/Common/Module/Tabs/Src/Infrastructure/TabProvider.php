<?php

namespace UserSpace\Common\Module\Tabs\Src\Infrastructure;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;

class TabProvider
{
    public function __construct(
        private readonly TabManager       $tabManager,
        private readonly TabConfigManager $tabConfigManager,
        private readonly array            $hardcodedTabClasses
    )
    {
    }

    public function registerDefaultTabs(): void
    {
        $config = $this->tabConfigManager->load() ?? [];
        $hardcodedClasses = $this->getHardcodedTabClasses();

        $configTabsByClass = [];
        foreach ($config as $tabData) {
            if (isset($tabData['class'])) {
                $configTabsByClass[$tabData['class']] = $tabData;
            }
        }

        // 1. Регистрируем все вкладки (и из конфига, и новые системные)
        $allClassesToRegister = array_unique(array_merge($hardcodedClasses, array_keys($configTabsByClass)));

        foreach ($allClassesToRegister as $class) {
            // Применяем сохраненную конфигурацию, если она есть для этого класса
            $tabConfig = $configTabsByClass[$class] ?? null;
            $this->tabManager->registerTab($class, $tabConfig);
        }

        // 2. Если были добавлены новые системные вкладки, которых не было в конфиге,
        // то обновляем конфиг, чтобы они появились в конструкторе.
        $newClasses = array_diff($hardcodedClasses, array_keys($configTabsByClass));
        if (!empty($newClasses)) {
            $this->updateConfigWithNewTabs();
        }
    }

    /**
     * Возвращает список всех "жестко" закодированных системных вкладок.
     *
     * @return array<int, class-string<AbstractTab>>
     */
    private function getHardcodedTabClasses(): array
    {
        return $this->hardcodedTabClasses;

    }

    /**
     * Обновляет сохраненную конфигурацию, добавляя в нее новые системные вкладки.
     */
    private function updateConfigWithNewTabs(): void
    {
        $allTabs = $this->tabManager->getAllRegisteredTabs(true); // Получаем плоский список
        $newConfig = [];
        foreach ($allTabs as $tab) {
            $tabData = $tab->toArray();
            $tabData['class'] = get_class($tab);
            $newConfig[] = $tabData;
        }
        $this->tabConfigManager->save($newConfig);
    }
}