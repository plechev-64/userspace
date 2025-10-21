<?php

namespace UserSpace\Admin;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;
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
        $groupedTabs = $this->groupTabs();

        $output = '<div class="usp-tabs-config-builder" data-usp-tab-builder>';

        // Рендерим все зарегистрированные локации, включая _unused
        foreach ($locations as $locationId => $locationLabel) {
            $tabsToRender = ($locationId === TabLocationManager::UNUSED_LOCATION)
                ? $groupedTabs['unassigned']
                : ($groupedTabs['assigned'][$locationId] ?? []);

            // Если для локации нет вкладок и это не _unused, то не рендерим ее
            if (empty($tabsToRender) && $locationId !== TabLocationManager::UNUSED_LOCATION) {
                continue;
            }

            $output .= $this->renderLocation($locationId, $locationLabel, $tabsToRender);
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Рендерит контейнер для одного места вывода.
     *
     * @param AbstractTab[] $tabs
     */
    private function renderLocation(string $id, string $label, array $tabs): string
    {
        // Если это _unused и в ней нет вкладок, то не рендерим ее
        if ($id === TabLocationManager::UNUSED_LOCATION && empty($tabs)) {
            return '';
        }

        $output = sprintf(
            '<div class="usp-tab-builder-location" data-location-id="%s">',
            $this->str->escAttr($id)
        );
        $output .= sprintf(
            '<h3 class="usp-tab-builder-location-title">%s</h3>',
            $this->str->escHtml($label)
        );
        $output .= '<div class="usp-tab-builder-tabs" data-sortable="tabs">';

        // Фильтруем и сортируем вкладки для этой локации
        // Здесь мы уже работаем с отфильтрованным массивом $tabs,
        // поэтому дополнительная фильтрация по parentId не нужна, если логика распределения
        // по $assignedTabs и $unassignedTabs уже учла иерархию.
        // Однако, если $allTabs возвращает плоский список, то эта фильтрация нужна.
        // Предполагаем, что $allTabs возвращает плоский список, и мы хотим отобразить только корневые вкладки здесь.
        $rootTabs = array_filter($tabs, fn(AbstractTab $tab) => $tab->getParentId() === null);
        usort($rootTabs, fn(AbstractTab $a, AbstractTab $b) => $a->getOrder() <=> $b->getOrder());

        foreach ($rootTabs as $tab) {
            $output .= $this->renderTab($tab);
        }

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Рендерит одну вкладку (и ее подвкладки).
     */
    private function renderTab(AbstractTab $tab): string
    {
        $configData = $tab->toArray();
        $configData['class'] = get_class($tab);
        $configJson = wp_json_encode($configData);

        // Вкладка может иметь дочерние, если у нее есть свой контент.
        $canHaveSubtabs = !empty(trim($tab->getContent()));
        // Класс для стилизации добавляем, только если дочерние вкладки уже есть.
        $hasSubtabsClass = !empty($tab->getSubTabs()) ? 'has-subtabs' : '';

        $output = sprintf(
            '<div class="usp-tab-builder-tab %s" data-id="%s" data-config="%s">',
            $this->str->escAttr($hasSubtabsClass),
            $this->str->escAttr($tab->getId()),
            $this->str->escAttr($configJson)
        );

        $output .= '<div class="usp-tab-builder-tab-header">';
        $output .= sprintf(
            '<span class="dashicons %s"></span> <span class="tab-title">%s</span>',
            $this->str->escAttr($tab->getIcon() ?? 'dashicons-admin-page'),
            $this->str->escHtml($tab->getTitle())
        );
        $output .= '<div class="usp-tab-builder-tab-actions">';
        $output .= '<button type="button" class="button button-small" data-action="edit-tab">' . $this->str->translate('Edit') . '</button>';
        $output .= '</div></div>';

        // Если вкладка может иметь дочерние, всегда рендерим для них контейнер (drop-зону).
        if ($canHaveSubtabs) {
            $output .= '<div class="usp-tab-builder-subtabs" data-sortable="subtabs">';
            $subTabs = $tab->getSubTabs();
            // Сортируем подвкладки
            usort($subTabs, fn(AbstractTab $a, AbstractTab $b) => $a->getOrder() <=> $b->getOrder());
            foreach ($subTabs as $subTab) {
                // Пропускаем рендеринг служебных "обзорных" вкладок
                if (str_ends_with($subTab->getId(), '_overview')) {
                    continue;
                }
                $output .= $this->renderTab($subTab);
            }
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Группирует все зарегистрированные вкладки на "присвоенные" и "бездомные".
     *
     * @return array{assigned: array<string, AbstractTab[]>, unassigned: AbstractTab[]}
     */
    private function groupTabs(): array
    {
        $allTabs = $this->tabManager->getAllRegisteredTabs();
        $assignedTabs = [];
        $unassignedTabs = [];

        foreach ($allTabs as $tab) {
            $location = $tab->getLocation();
            // Если локация вкладки зарегистрирована и это не служебная локация
            if ($this->tabLocationManager->isLocationRegistered($location) && $location !== TabLocationManager::UNUSED_LOCATION) {
                $assignedTabs[$location][] = $tab;
            } else {
                $unassignedTabs[] = $tab;
            }
        }

        return ['assigned' => $assignedTabs, 'unassigned' => $unassignedTabs];
    }
}