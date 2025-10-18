<?php

namespace UserSpace\Admin;

use UserSpace\Core\Tabs\TabDto;

/**
 * Генерирует HTML-представление конструктора вкладок.
 */
class TabConfigBuilder
{
    /**
     * @param array<string, string> $locations
     * @param TabDto[] $tabs
     */
    public function __construct(
        private array $locations,
        private array $tabs
    ) {
    }

    /**
     * Генерирует HTML-код конструктора.
     */
    public function render(): string
    {
        $output = '<div class="usp-tabs-config-builder" data-usp-tab-builder>';

        foreach ($this->locations as $locationId => $locationLabel) {
            $output .= $this->renderLocation($locationId, $locationLabel);
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Рендерит контейнер для одного места вывода.
     */
    private function renderLocation(string $id, string $label): string
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
        $locationTabs = array_filter($this->tabs, fn($tab) => $tab->location === $id && $tab->parentId === null);
        usort($locationTabs, fn($a, $b) => $a->order <=> $b->order);

        foreach ($locationTabs as $tab) {
            $output .= $this->renderTab($tab);
        }

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Рендерит одну вкладку (и ее подвкладки).
     */
    private function renderTab(TabDto $tab): string
    {
        $configData = (array)$tab;
        // Исключаем не-сериализуемые свойства перед передачей на фронтенд
        unset($configData['contentSource'], $configData['subTabs']);

        $configJson      = wp_json_encode($configData);
        $hasSubtabsClass = !empty($tab->subTabs) ? 'has-subtabs' : '';

        $output = sprintf(
            '<div class="usp-tab-builder-tab %s" data-id="%s" data-config="%s">',
            esc_attr($hasSubtabsClass),
            esc_attr($tab->id),
            esc_attr($configJson)
        );

        $output .= '<div class="usp-tab-builder-tab-header">';
        $output .= sprintf(
            '<span class="dashicons %s"></span> <span class="tab-title">%s</span>',
            esc_attr($tab->icon ?? 'dashicons-admin-page'),
            esc_html($tab->title)
        );
        $output .= '<div class="usp-tab-builder-tab-actions">';
        $output .= '<button type="button" class="button button-small" data-action="edit-tab">' . esc_html__('Edit', 'usp') . '</button>';
        $output .= '</div></div>';

        if (!empty($tab->subTabs)) {
            $output .= '<div class="usp-tab-builder-subtabs" data-sortable="subtabs">';
            // Сортируем подвкладки
            usort($tab->subTabs, fn($a, $b) => $a->order <=> $b->order);
            foreach ($tab->subTabs as $subTab) {
                $output .= $this->renderTab($subTab);
            }
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }
}