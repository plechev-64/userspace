<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\TabConfigBuilder;
use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Core\Tabs\TabConfigManager;
use UserSpace\Core\Tabs\TabLocationManager;
use UserSpace\Core\Tabs\TabManager;

class TabsConfigPage extends AbstractAdminPage
{
    public function __construct(
        private readonly TabManager $tabManager,
        private readonly TabLocationManager $tabLocationManager,
        private readonly TabConfigManager $tabConfigManager
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
        // Загружаем сохраненную конфигурацию или берем дефолтную из TabManager
        $config = $this->tabConfigManager->load();
        if (null === $config) {
            // Если конфига нет, генерируем его из того, что зарегистрировано в TabProvider
            $registeredTabs = $this->tabManager->getAllRegisteredTabs();
            $config = [];
            foreach (array_merge($registeredTabs, ...array_column($registeredTabs, 'subTabs')) as $tab) {
                $tabData = (array)$tab;
                unset($tabData['contentSource'], $tabData['subTabs']);
                $config[] = $tabData;
            }
            // И сразу сохраняем, чтобы при следующей загрузке он уже был
            $this->tabConfigManager->save($config);
        }

        // Создаем DTO из конфигурации для передачи в билдер
        $tabs = [];
        foreach ($config as $tabData) {
            $tabDto = new \UserSpace\Core\Tabs\TabDto($tabData['id'], $tabData['title'], $tabData['parentId'] ?? null);
            foreach ($tabData as $key => $value) {
                if (property_exists($tabDto, $key)) {
                    $tabDto->{$key} = $value;
                }
            }
            $tabs[$tabDto->id] = $tabDto;
        }

        // Строим иерархию
        $tabsCopy = $tabs;
        // Строим иерархию
        foreach ($tabsCopy as $tab) {
            if ($tab->parentId && isset($tabs[$tab->parentId])) {
                $tabs[$tab->parentId]->subTabs[] = $tab;
                unset($tabs[$tab->id]);
            }
        }

        $locations = $this->tabLocationManager->getRegisteredLocations();
        $builder = new TabConfigBuilder($locations, $tabs);

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->getPageTitle()) . '</h1>';
        echo '<p>' . __('Here you will be able to configure the tabs of the user profile.', 'usp') . '</p>';

        echo '<div id="usp-tab-builder-notifications"></div>';

        echo $builder->render();

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