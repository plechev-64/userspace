<?php

namespace UserSpace\Common\Module\Locations\Src\Infrastructure;

use UserSpace\Common\Module\Locations\Src\Domain\ItemManagerInterface;
use UserSpace\Common\Module\Locations\Src\Domain\ItemProviderInterface;
use UserSpace\Common\Module\Locations\Src\Domain\ItemRegistryInterface;

class ItemProvider implements ItemProviderInterface
{
    public function __construct(
        private readonly ItemManagerInterface  $itemManager,
        private readonly LocationConfigManager $locationConfigManager,
        private readonly ItemRegistryInterface $itemRegistry
    )
    {
    }

    public function mergeRegisteredItemsAndConfig(): void
    {
        $hardcodedClasses = $this->itemRegistry->getRegisteredItems();
        $config = $this->locationConfigManager->load() ?? [];

        $configItemsByClass = [];
        foreach ($config as $itemData) {
            if (isset($itemData['class'])) {
                $configItemsByClass[$itemData['class']] = $itemData;
            }
        }

        // 1. Регистрируем все элементы (и из конфига, и новые системные)
        $allClassesToRegister = array_unique(array_merge($hardcodedClasses, array_keys($configItemsByClass)));

        foreach ($allClassesToRegister as $class) {
            // Применяем сохраненную конфигурацию, если она есть для этого класса
            $itemConfig = $configItemsByClass[$class] ?? null;
            $this->itemManager->loadItem($class, $itemConfig);
        }

        // 2. Если были добавлены новые системные элементы, которых не было в конфиге,
        // то обновляем конфиг, чтобы они появились в конструкторе.
        $newClasses = array_diff($hardcodedClasses, array_keys($configItemsByClass));
        if (!empty($newClasses)) {
            $this->updateConfigWithNewItems();
        }
    }

    /**
     * Обновляет сохраненную конфигурацию, добавляя в нее новые системные элементы.
     */
    private function updateConfigWithNewItems(): void
    {
        $allItems = $this->itemManager->getAllRegisteredItems(true); // Получаем плоский список
        $newConfig = [];
        foreach ($allItems as $item) {
            $itemData = $item->toArray();
            $itemData['class'] = get_class($item);
            $newConfig[] = $itemData;
        }
        $this->locationConfigManager->save($newConfig);
    }
}