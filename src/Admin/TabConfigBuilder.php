<?php

namespace UserSpace\Admin;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;
use UserSpace\Common\Module\Tabs\Src\Domain\ItemInterface;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabLocationManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Генерирует HTML-представление конструктора вкладок.
 */
class TabConfigBuilder
{
    public function __construct(
        private readonly TabManager            $tabManager,
        private readonly TabLocationManager    $tabLocationManager,
        private readonly StringFilterInterface $str
    )
    {
    }

    /**
     * Генерирует HTML-код конструктора.
     */
    final public function render(): string
    {
        $locations = $this->tabLocationManager->getRegisteredLocations(); // Получаем все локации, включая _unused
        $groupedItems = $this->groupItems();

        $output = '<div class="usp-tabs-config-builder" data-usp-tab-builder>';

        // Рендерим все зарегистрированные локации, включая _unused
        foreach ($locations as $locationId => $locationLabel) {
            $itemsToRender = ($locationId === TabLocationManager::UNUSED_LOCATION)
                ? $groupedItems['unassigned']
                : ($groupedItems['assigned'][$locationId] ?? []);

            $output .= $this->renderLocation($locationId, $locationLabel, $itemsToRender);
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Рендерит контейнер для одного места вывода.
     *
     * @param ItemInterface[] $items
     */
    private function renderLocation(string $id, string $label, array $items): string
    {

        $output = sprintf(
            '<div class="usp-tab-builder-location" data-location-id="%s">',
            $this->str->escAttr($id)
        );
        $output .= sprintf(
            '<h3 class="usp-tab-builder-location-title">%s</h3>',
            $this->str->escHtml($label)
        );
        $output .= '<div class="usp-tab-builder-tabs" data-sortable="tabs">';

        // Отображаем только корневые элементы для данной локации
        $rootItems = array_filter($items, fn(ItemInterface $item) => $item->getParentId() === null);
        usort($rootItems, fn(ItemInterface $a, ItemInterface $b) => $a->getOrder() <=> $b->getOrder());

        foreach ($rootItems as $item) {
            $output .= $this->renderItem($item);
        }

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Рендерит один элемент меню (вкладку или кнопку).
     */
    private function renderItem(ItemInterface $item): string
    {
        $configData = $item->toArray();
        $configData['class'] = get_class($item);
        $configJson = wp_json_encode($configData);

        $itemType = $item->getItemType();
        $isTab = $itemType === 'tab';

        // Только вкладки могут иметь дочерние элементы.
        $canHaveSubItems = false;
        $hasSubItemsClass = '';
        if ($isTab && $item instanceof AbstractTab) {
            // Вкладка может иметь дочерние, если у нее есть свой контент и она не является "обзорной".
            $canHaveSubItems = !empty(trim($item->getContent())) && !str_ends_with($item->getId(), '_overview');
            // Класс для стилизации добавляем, только если дочерние элементы уже есть.
            $hasSubItemsClass = !empty($item->getSubTabs()) ? 'has-sub-items' : '';
        }

        $output = sprintf(
            '<div class="usp-tab-builder-item usp-tab-builder-item--%s %s" data-id="%s" data-config="%s">',
            $this->str->escAttr($itemType),
            $this->str->escAttr($hasSubItemsClass),
            $this->str->escAttr($item->getId()),
            $this->str->escAttr($configJson)
        );

        $output .= '<div class="usp-tab-builder-item-header">';
        $output .= sprintf(
            '<span class="dashicons %s"></span> <span class="tab-title">%s</span>',
            $this->str->escAttr($item->getIcon() ?? ($isTab ? 'dashicons-admin-page' : 'dashicons-admin-generic')),
            $this->str->escHtml($item->getTitle())
        );
        $output .= '<div class="usp-tab-builder-item-actions">';
        $output .= '<button type="button" class="button button-small" data-action="edit-tab">' . $this->str->translate('Edit') . '</button>';
        $output .= '</div></div>';

        // Если это вкладка и она может иметь дочерние элементы, рендерим контейнер для них.
        if ($canHaveSubItems) {
            $output .= '<div class="usp-tab-builder-sub-items" data-sortable="subitems">';
            $subItems = ($item instanceof AbstractTab) ? $item->getSubTabs() : []; // Дополнительная проверка, хотя $canHaveSubItems уже гарантирует, что это AbstractTab
            // Сортируем дочерние элементы
            usort($subItems, fn(ItemInterface $a, ItemInterface $b) => $a->getOrder() <=> $b->getOrder());
            foreach ($subItems as $subItem) {
                // Пропускаем рендеринг служебных "обзорных" вкладок
                if (str_ends_with($subItem->getId(), '_overview')) {
                    continue;
                }
                $output .= $this->renderItem($subItem);
            }
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Группирует все зарегистрированные элементы на "присвоенные" и "бездомные".
     *
     * @return array{assigned: array<string, ItemInterface[]>, unassigned: ItemInterface[]}
     */
    private function groupItems(): array
    {
        $allItems = $this->tabManager->getAllRegisteredItems(true); // Получаем плоский список
        $assignedItems = [];
        $unassignedItems = [];

        foreach ($allItems as $item) {
            $location = $item->getLocation();
            // Если локация вкладки зарегистрирована и это не служебная локация
            if ($this->tabLocationManager->isLocationRegistered($location) && $location !== TabLocationManager::UNUSED_LOCATION) {
                $assignedItems[$location][] = $item;
            } else {
                $unassignedItems[] = $item;
            }
        }

        return ['assigned' => $assignedItems, 'unassigned' => $unassignedItems];
    }
}