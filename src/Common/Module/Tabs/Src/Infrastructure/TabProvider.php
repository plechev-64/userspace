<?php

namespace UserSpace\Common\Module\Tabs\Src\Infrastructure;

use UserSpace\Common\Module\Tabs\Src\Domain\ItemInterface;

class TabProvider
{
    /**
     * @param array<int, class-string<ItemInterface>> $hardcodedItemClasses
     */
    public function __construct(
        private readonly TabManager       $tabManager,
        private readonly TabConfigManager $tabConfigManager,
        private readonly array            $hardcodedItemClasses
    )
    {
    }

    public function registerDefaultTabs(): void
    {
        $config = $this->tabConfigManager->load() ?? [];

        $hardcodedClasses = $this->getHardcodedItemClasses();

        $configTabsByClass = [];
        foreach ($config as $tabData) {
            if (isset($tabData['class'])) {
                $configTabsByClass[$tabData['class']] = $tabData;
            }
        }

        // 1. Регистрируем все элементы (и из конфига, и новые системные)
        $allClassesToRegister = array_unique(array_merge($hardcodedClasses, array_keys($configTabsByClass)));

        foreach ($allClassesToRegister as $class) {
            // Применяем сохраненную конфигурацию, если она есть для этого класса
            $tabConfig = $configTabsByClass[$class] ?? null;
            $this->tabManager->registerItem($class, $tabConfig);
        }

        // 2. Если были добавлены новые системные элементы, которых не было в конфиге,
        // то обновляем конфиг, чтобы они появились в конструкторе.
        $newClasses = array_diff($hardcodedClasses, array_keys($configTabsByClass));
        if (!empty($newClasses)) {
            $this->updateConfigWithNewItems();
        }
    }

    /**
     * Возвращает список всех "жестко" закодированных системных элементов (вкладок и кнопок).
     *
     * @return array<int, class-string<ItemInterface>>
     */
    private function getHardcodedItemClasses(): array
    {
        return $this->hardcodedItemClasses;

    }

    /**
     * Обновляет сохраненную конфигурацию, добавляя в нее новые системные элементы.
     */
    private function updateConfigWithNewItems(): void
    {
        $allItems = $this->tabManager->getAllRegisteredItems(true); // Получаем плоский список
        $newConfig = [];
        foreach ($allItems as $item) {
            $itemData = $item->toArray();
            $itemData['class'] = get_class($item);
            $newConfig[] = $itemData;
        }
        $this->tabConfigManager->save($newConfig);
    }
}