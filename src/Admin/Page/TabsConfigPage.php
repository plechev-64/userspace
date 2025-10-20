<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Admin\TabConfigBuilder;
use UserSpace\Core\AdminApiInterface;
use UserSpace\Core\AssetRegistryInterface;
use UserSpace\Core\StringFilterInterface;

class TabsConfigPage extends AbstractAdminPage
{
    public function __construct(
        private readonly TabConfigBuilder       $tabConfigBuilder,
        private readonly StringFilterInterface  $str,
        private readonly AssetRegistryInterface $assetRegistry,
        AdminApiInterface                       $adminApi
    )
    {
        parent::__construct($adminApi);
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
        $this->assetRegistry->enqueueStyle('usp-form-builder', USERSPACE_PLUGIN_URL . 'assets/css/tab-builder.css', [], USERSPACE_VERSION);
        $this->assetRegistry->enqueueStyle('usp-modal', USERSPACE_PLUGIN_URL . 'assets/css/modal.css', [], USERSPACE_VERSION);
        $this->assetRegistry->enqueueStyle('usp-form', USERSPACE_PLUGIN_URL . 'assets/css/form.css', [], USERSPACE_VERSION);

        // SortableJS
        $this->assetRegistry->enqueueScript(
            'sortable-js',
            'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js',
            [],
            null,
            true
        );

        // JS конструктора вкладок
        $this->assetRegistry->enqueueScript(
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
        echo '<h1>' . $this->str->escHtml($this->adminApi->getAdminPageTitle()) . ' <a href="#" id="usp-create-new-tab" class="page-title-action">' . $this->str->translate('Add New Tab') . '</a></h1>';
        echo '<p>' . $this->str->translate('Here you will be able to configure the tabs of the user profile.') . '</p>';

        echo '<div id="usp-tab-builder-notifications"></div>';

        echo $this->tabConfigBuilder->render();

        echo '<p class="submit">';
        echo '<button type="button" id="usp-save-tab-builder" class="button button-primary">' . $this->str->translate('Save Changes') . '</button>';
        echo '</p>';

        // TODO: Подключить JS и шаблоны
        echo '</div>';
    }

    public function getPageTitle(): string
    {
        return $this->str->translate('Tabs Configuration');
    }

    protected function getMenuTitle(): string
    {
        return $this->str->translate('Tabs');
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