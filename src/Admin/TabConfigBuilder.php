<?php

namespace UserSpace\Admin;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabLocationManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;

/**
 * Генерирует HTML-представление конструктора вкладок.
 */
class TabConfigBuilder
{
    /**
     */
    public function __construct(
        private readonly TabManager $tabManager,
        private readonly TabLocationManager $tabLocationManager
    ) {
    }

    /**
     * Генерирует HTML-код конструктора.
     */
    final public function render(): string
    {
        // Получаем все зарегистрированные вкладки и локации
        $tabs = $this->tabManager->getAllRegisteredTabs();
        $locations = $this->tabLocationManager->getRegisteredLocations();

        $output = '<div class="usp-tabs-config-builder" data-usp-tab-builder>';

        foreach ($locations as $locationId => $locationLabel) {
            $output .= $this->renderLocation($locationId, $locationLabel, $tabs);
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
        $output = sprintf(
            '<div class="usp-tab-builder-location" data-location-id="%s">',
            esc_attr($id)
        );
        $output .= sprintf(
            '<h3 class="usp-tab-builder-location-title">%s</h3>',
            esc_html($label)
        );
        $output .= '<div class="usp-tab-builder-tabs" data-sortable="tabs">';

        // Фильтруем и сортируем вкладки для этой локации
        $locationTabs = array_filter($tabs, fn(AbstractTab $tab) => $tab->getLocation() === $id && $tab->getParentId() === null);
        usort($locationTabs, fn(AbstractTab $a, AbstractTab $b) => $a->getOrder() <=> $b->getOrder());

        foreach ($locationTabs as $tab) {
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
        $configData      = $tab->toArray();
        $configData['class'] = get_class($tab);
        $configJson = wp_json_encode($configData);

        // Вкладка может иметь дочерние, если у нее есть свой контент.
        $canHaveSubtabs = ! empty(trim($tab->getContent()));
        // Класс для стилизации добавляем, только если дочерние вкладки уже есть.
        $hasSubtabsClass = ! empty($tab->getSubTabs()) ? 'has-subtabs' : '';

        $output = sprintf(
            '<div class="usp-tab-builder-tab %s" data-id="%s" data-config="%s">',
            esc_attr($hasSubtabsClass),
            esc_attr($tab->getId()),
            esc_attr($configJson)
        );

        $output .= '<div class="usp-tab-builder-tab-header">';
        $output .= sprintf(
            '<span class="dashicons %s"></span> <span class="tab-title">%s</span>',
            esc_attr($tab->getIcon() ?? 'dashicons-admin-page'),
            esc_html($tab->getTitle())
        );
        $output .= '<div class="usp-tab-builder-tab-actions">';
        $output .= '<button type="button" class="button button-small" data-action="edit-tab">' . esc_html__('Edit', 'usp') . '</button>';
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
}