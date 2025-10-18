<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Admin\TabConfigBuilder;

class TabsConfigPage extends AbstractAdminPage
{
    public function __construct(
        private readonly TabConfigBuilder $tabConfigBuilder
    ) {
    }

    /**
     * Подключает CSS и JS для страницы конструктора вкладок.
     * @param string $hook Текущий hook страницы.
     */
    final public function enqueueAssets(string $hook): void
    {
        if ($this->hookSuffix !== $hook) {
            return;
        }

        // Стили для конструктора и модальных окон
        wp_enqueue_style('usp-form-builder', USERSPACE_PLUGIN_URL . 'assets/css/tab-builder.css', [], USERSPACE_VERSION);
        wp_enqueue_style('usp-modal', USERSPACE_PLUGIN_URL . 'assets/css/modal.css', [], USERSPACE_VERSION);
        wp_enqueue_style('usp-form', USERSPACE_PLUGIN_URL . 'assets/css/form.css', [], USERSPACE_VERSION);

        // SortableJS
        wp_enqueue_script(
            'sortable-js',
            'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js',
            [],
            null,
            true
        );

        // JS конструктора вкладок
        wp_enqueue_script(
            'usp-tab-builder-js',
            USERSPACE_PLUGIN_URL . 'assets/js/tab-builder.js',
            ['usp-core', 'sortable-js'],
            USERSPACE_VERSION,
            true
        );
    }

    /**
     * Рендерит HTML-код страницы.
     * Здесь будет наш новый конструктор.
     */
    public function render(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->getPageTitle()) . ' <a href="#" id="usp-create-new-tab" class="page-title-action">' . esc_html__('Add New Tab', 'usp') . '</a></h1>';
        echo '<p>' . __('Here you will be able to configure the tabs of the user profile.', 'usp') . '</p>';

        echo '<div id="usp-tab-builder-notifications"></div>';

        echo $this->tabConfigBuilder->render();

        echo '<p class="submit">';
        echo '<button type="button" id="usp-save-tab-builder" class="button button-primary">' . __('Save Changes', 'usp') . '</button>';
        echo '</p>';

        // TODO: Подключить JS и шаблоны
        echo '</div>';
    }

    protected function getPageTitle(): string
    {
        return __('Tabs Configuration', 'usp');
    }

    protected function getMenuTitle(): string
    {
        return __('Tabs', 'usp');
    }

    protected function getMenuSlug(): string
    {
        return 'userspace-tabs-config';
    }

    protected function getParentSlug(): ?string
    {
        return 'userspace-settings';
    }
}