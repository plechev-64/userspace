<?php

namespace UserSpace\Common\Module\Locations\Src\Infrastructure;

use UserSpace\Common\Module\Locations\Src\Domain\ItemInterface;
use UserSpace\Common\Module\Locations\Src\Domain\ItemRegistryInterface;

class ItemRegistry implements ItemRegistryInterface
{
    /**
     * @var array<int, class-string<ItemInterface>>
     */
    private array $registeredItems = [];

    public function registerItem(string $itemClassName): void
    {
        if (is_subclass_of($itemClassName, ItemInterface::class) && !in_array($itemClassName, $this->registeredItems, true)) {
            $this->registeredItems[] = $itemClassName;
        }
    }

    public function getRegisteredItems(): array
    {
        return $this->registeredItems;
    }
}