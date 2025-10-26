<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Page\Abstract\AbstractAdminPage;
use UserSpace\Admin\Service\SettingsFormConfigServiceInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\Factory\FormFactory;
use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Common\Module\Settings\Src\Domain\PluginSettings;
use UserSpace\Core\Admin\AdminApiInterface;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Управляет главной страницей настроек плагина.
 */
class SettingsPage extends AbstractAdminPage
{
    private const OPTION_GROUP = 'usp_settings_group';

    public function __construct(
        private readonly FormFactory                        $formFactory,
        private readonly StringFilterInterface              $str,
        private readonly OptionManagerInterface             $optionManager,
        private readonly AssetRegistryInterface             $assetRegistry,
        private readonly SettingsFormConfigServiceInterface $settingsFormConfigService,
        AdminApiInterface                                   $adminApi,
        HookManagerInterface                                $hookManager
    )
    {
        parent::__construct($adminApi, $hookManager);
    }

    /**
     * Регистрирует настройки для сохранения через Settings API.
     */
    public function registerSettings(): void
    {
        $this->optionManager->register(self::OPTION_GROUP, PluginSettings::OPTION_NAME);
    }

    /**
     * Подключает ассеты для страницы настроек.
     * @param string $hook
     */
    public function enqueueAssets(string $hook): void
    {
        if ($this->hookSuffix !== $hook) {
            return;
        }

        $this->assetRegistry->enqueueScript('usp-uploader-handler');

        $this->assetRegistry->enqueueStyle('usp-form-style', USERSPACE_PLUGIN_URL . 'assets/css/form.css', [], USERSPACE_VERSION);

        $this->assetRegistry->enqueueStyle(
            'usp-admin-settings',
            USERSPACE_PLUGIN_URL . 'assets/css/admin-settings.css',
            [],
            USERSPACE_VERSION
        );

        $this->assetRegistry->enqueueScript(
            'usp-admin-settings-js',
            USERSPACE_PLUGIN_URL . 'assets/js/admin-settings.js',
            [],
            USERSPACE_VERSION,
            true
        );

        $this->assetRegistry->localizeScript(
            'usp-admin-settings-js',
            'uspApiSettings',
            [
                'root' => $this->str->escUrlRaw(rest_url()),
                'namespace' => USERSPACE_REST_NAMESPACE,
                'nonce' => wp_create_nonce('wp_rest'),
            ]
        );

        $this->assetRegistry->localizeScript(
            'usp-admin-settings-js',
            'uspL10n',
            [
                'adminSettings' => [
                    'saving' => $this->str->translate('Saving...'),
                    'networkError' => $this->str->translate('Network error occurred.'),
                ],
            ]
        );
    }

    /**
     * Рендерит страницу настроек.
     */
    public function render(): void
    {
        $formConfig = $this->settingsFormConfigService->getFormConfig();

        echo '<div class="wrap usp-settings-wrap">';
        echo '<h1>' . $this->str->escHtml($this->adminApi->getAdminPageTitle()) . '</h1>';

        echo '<div id="usp-settings-notifications"></div>';

        echo '<div class="usp-settings-layout">';

        // Меню табов
        echo '<ul class="usp-settings-tabs-menu">';
        foreach ($formConfig->toArray()['sections'] as $index => $section) {
            $id = $section['id'] ?? 'section-' . $index;
            $class = $index === 0 ? 'active' : '';
            echo '<li><a href="#' . $this->str->escAttr($id) . '" class="' . $this->str->escAttr($class) . '">' . $this->str->escHtml($section['title']) . '</a></li>';
        }
        echo '</ul>';

        // Контент табов
        echo '<div class="usp-settings-tabs-content">';
        echo '<div id="usp-settings-form-wrapper">'; // Обертка вместо <form>

        $allSections = $formConfig->toArray()['sections'];
        foreach ($allSections as $index => $section) {
            $id = $section['id'] ?? 'section-' . $index;
            $class = $index === 0 ? 'active' : '';
            echo '<div id="' . $this->str->escAttr($id) . '" class="usp-tab-pane ' . $this->str->escAttr($class) . '">';

            // Создаем FormConfig для ОДНОЙ текущей секции, чтобы отрендерить ее отдельно
            $sectionFormConfig = new FormConfig();
            $sectionFormConfig->addSection($section['title']);
            foreach ($section['blocks'] as $block) {
                $sectionFormConfig->addBlock($block['title']);
                foreach ($block['fields'] as $name => $fieldData) {
                    $sectionFormConfig->addField($name, $fieldData);
                }
            }
            $sectionForm = $this->formFactory->create($sectionFormConfig);
            // Вместо рендеринга всей формы, рендерим каждый блок и его поля
            // Это позволяет избежать проблемы с двойной оберткой usp-form-field-wrapper
            foreach ($sectionForm->getSections() as $formSection) {
                foreach ($formSection->getBlocks() as $block) {
                    echo '<div class="usp-form-block">'; // Обертка для блока
                    echo '<h4 class="usp-form-block-title">' . $this->str->escHtml($block->getTitle()) . '</h4>'; // Заголовок блока
                    foreach ($block->getFields() as $field) {
                        echo $field->render(); // Рендерим каждое поле индивидуально
                    }
                    echo '</div>'; // Закрываем usp-form-block
                }
            }
            echo '</div>';
        }

        echo '</div>'; // #usp-settings-form-wrapper

        echo '<p class="submit"><button type="button" id="usp-save-settings" class="button button-primary">' . $this->str->translate('Save Settings') . '</button></p>';

        echo '</div>'; // .usp-settings-tabs-content

        echo '</div>'; // .usp-settings-layout
        echo '</div>'; // .wrap
    }

    protected function getPageTitle(): string
    {
        return $this->str->translate('UserSpace Settings');
    }

    protected function getMenuTitle(): string
    {
        return 'UserSpace';
    }

    protected function getMenuSlug(): string
    {
        return 'userspace-settings';
    }

    protected function getIcon(): string
    {
        return 'dashicons-admin-users';
    }

    protected function getPosition(): ?int
    {
        return 30;
    }
}