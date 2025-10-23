<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Page\Abstract\AbstractAdminPage;
use UserSpace\Admin\SettingsConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\BooleanAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\CheckboxAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\RadioAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextareaAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UploaderAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UrlAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Core\Admin\AdminApiInterface;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\Theme\ThemeManagerInterface;

/**
 * Управляет главной страницей настроек плагина.
 */
class SettingsPage extends AbstractAdminPage
{
    private const OPTION_GROUP = 'usp_settings_group';
    private const OPTION_NAME = 'usp_settings';

    public function __construct(
        private readonly FormFactory            $formFactory,
        private readonly ThemeManagerInterface           $themeManager,
        private readonly SettingsConfig         $settingsConfig,
        private readonly StringFilterInterface  $str,
        private readonly OptionManagerInterface $optionManager,
        private readonly AssetRegistryInterface $assetRegistry,
        AdminApiInterface                       $adminApi,
        HookManagerInterface                    $hookManager
    )
    {
        parent::__construct($adminApi, $hookManager);
    }

    /**
     * Регистрирует настройки для сохранения через Settings API.
     */
    public function registerSettings(): void
    {
        $this->optionManager->register(self::OPTION_GROUP, self::OPTION_NAME);
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
        $settingsConfig = $this->getSettingsConfig();
        $config = $settingsConfig->toArray();
        $options = $this->optionManager->get(self::OPTION_NAME, []);

        $formConfig = new FormConfig();
        foreach ($config['sections'] as $section) {
            $formConfig->addSection($section['title']);

            foreach ($section['blocks'] as $block) {
                $formConfig->addBlock($block['title']);

                foreach ($block['fields'] as $name => $fieldData) {
                    if (isset($options[$name])) {
                        $fieldData['value'] = $options[$name];
                    }
                    $formConfig->addField($name, $fieldData);
                }
            }
        }

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

    /**
     * Собирает конфигурацию для формы настроек через фильтр.
     */
    private function getSettingsConfig(): SettingsConfig
    {
        $config = $this->settingsConfig
            //-- Section
            ->addSection('general', $this->str->translate('General'))
            ->addBlock('main', $this->str->translate('Main Settings'))
            ->addOption(new TextAbstractFieldDto('api_key', ['label' => $this->str->translate('API Key')]))
            ->addOption(new BooleanAbstractFieldDto('enable_feature_x', ['label' => $this->str->translate('Enable Feature X')]))
            ->addOption(new UploaderAbstractFieldDto('default_avatar_id', [
                'label' => $this->str->translate('Default Avatar'),
                'allowed_types' => 'image/jpeg',
                'image_max_width' => 500,
            ]))
            ->addOption(new UploaderAbstractFieldDto('files', [
                'label' => $this->str->translate('Files'),
                'allowed_types' => 'image/jpeg',
                'multiple' => true,
            ]))
            ->addOption(new BooleanAbstractFieldDto('enable_user_bar', ['label' => $this->str->translate('Enable User Bar at the top of the site')]))
            ->addOption(new BooleanAbstractFieldDto('require_email_confirmation', ['label' => $this->str->translate('Require email confirmation for registration')]))
            ->addOption(new CheckboxAbstractFieldDto('prefer_color', [
                'label' => $this->str->translate('Prefer color'),
                'options' => [
                    'white' => $this->str->translate('White'),
                    'black' => $this->str->translate('Black'),
                    'green' => $this->str->translate('Green')
                ],
            ]))
            //-- Section
            ->addSection('advanced', $this->str->translate('Advanced'))
            ->addBlock('integration', $this->str->translate('Integration'))
            ->addOption(new SelectAbstractFieldDto('integration_mode', [
                'label' => $this->str->translate('Integration Mode'),
                'options' => ['mode1' => $this->str->translate('Mode 1'), 'mode2' => $this->str->translate('Mode 2')],
            ]))
            ->addOption(new UrlAbstractFieldDto('webhook_url', ['label' => $this->str->translate('Webhook URL')]))
            ->addBlock('other', $this->str->translate('Other'))
            ->addOption(new RadioAbstractFieldDto('user_role', [
                'label' => $this->str->translate('Default Role'),
                'options' => ['subscriber' => $this->str->translate('Subscriber'), 'editor' => $this->str->translate('Editor')],
            ]))
            ->addOption(new TextareaAbstractFieldDto('custom_css', ['label' => $this->str->translate('Custom CSS')]))
            //-- Section
            ->addSection('page_settings', $this->str->translate('Page Assignment'))
            ->addBlock('core_pages', $this->str->translate('Core Pages'))
            ->addOption(new SelectAbstractFieldDto('login_page_id', [
                'label' => $this->str->translate('Login Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectAbstractFieldDto('registration_page_id', [
                'label' => $this->str->translate('Registration Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectAbstractFieldDto('password_reset_page_id', [
                'label' => $this->str->translate('Password Recovery Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectAbstractFieldDto('profile_page_id', [
                'label' => $this->str->translate('User Profile Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new TextAbstractFieldDto('profile_user_query_var', [
                'label' => $this->str->translate('User ID Query Variable'),
                'description' => $this->str->translate('The GET parameter in the URL to identify the user. Default: <code>user_id</code>.'),
            ]))
            ->addOption(new TextAbstractFieldDto('profile_tab_query_var', [
                'label' => $this->str->translate('Profile Tab Query Variable'),
                'description' => $this->str->translate('The GET parameter in the URL to identify the profile tab. Default: <code>tab</code>.'),
            ]))
            // NEW: Example Parent-Child Settings
            ->addSection('dependency_examples', $this->str->translate('Dependency Examples'))
            ->addBlock('parent_fields', $this->str->translate('Parent Fields'))
            ->addOption(new SelectAbstractFieldDto('parent_select_field', [
                'label' => $this->str->translate('Select an Option'),
                'options' => [
                    'option1' => $this->str->translate('Option 1 (shows text field)'),
                    'option2' => $this->str->translate('Option 2 (shows checkbox)'),
                    'option3' => $this->str->translate('Option 3 (shows radio)'),
                ],
                'description' => $this->str->translate('Select a value to reveal dependent fields.'),
            ]))
            ->addOption(new BooleanAbstractFieldDto('parent_checkbox_field', [
                'label' => $this->str->translate('Enable Feature Y'),
                'description' => $this->str->translate('Check this to reveal a dependent text area.'),
            ]))
            ->addOption(new RadioAbstractFieldDto('parent_radio_field', [
                'label' => $this->str->translate('Choose a Type'),
                'options' => [
                    'typeA' => $this->str->translate('Type A (shows URL field)'),
                    'typeB' => $this->str->translate('Type B (shows uploader)'),
                ],
                'description' => $this->str->translate('Select a type to reveal dependent fields.'),
            ]))
            ->addBlock('dependent_fields', $this->str->translate('Dependent Fields'))
            ->addOption(new TextAbstractFieldDto('dependent_text_field', [
                'label' => $this->str->translate('Text Field for Option 1'),
                'description' => $this->str->translate('This field appears when "Option 1" is selected.'),
                'dependency' => [
                    'parent_field' => 'parent_select_field',
                    'parent_value' => 'option1',
                    'type' => 'select',
                ],
            ]))
            ->addOption(new CheckboxAbstractFieldDto('dependent_checkbox_field', [
                'label' => $this->str->translate('Checkbox for Option 2'),
                'description' => $this->str->translate('This checkbox appears when "Option 2" is selected.'),
                'options' => [
                    'checkA' => $this->str->translate('check A'),
                    'checkB' => $this->str->translate('check B'),
                ],
                'dependency' => [
                    'parent_field' => 'parent_select_field',
                    'parent_value' => 'option2',
                    'type' => 'select',
                ],
            ]))
            ->addOption(new RadioAbstractFieldDto('dependent_radio_field', [
                'label' => $this->str->translate('Radio for Option 3'),
                'options' => [
                    'sub_option_a' => $this->str->translate('Sub Option A'),
                    'sub_option_b' => $this->str->translate('Sub Option B'),
                ],
                'description' => $this->str->translate('These radio buttons appear when "Option 3" is selected.'),
                'dependency' => [
                    'parent_field' => 'parent_select_field',
                    'parent_value' => 'option3',
                    'type' => 'select',
                ],
            ]))
            ->addOption(new TextareaAbstractFieldDto('dependent_textarea_field', [
                'label' => $this->str->translate('Text Area for Feature Y'),
                'description' => $this->str->translate('This text area appears when "Enable Feature Y" is checked.'),
                'dependency' => [
                    'parent_field' => 'parent_checkbox_field',
                    'parent_value' => true, // Boolean for checkbox
                    'type' => 'checkbox',
                ],
            ]))
            ->addOption(new UrlAbstractFieldDto('dependent_url_field', [
                'label' => $this->str->translate('URL Field for Type A'),
                'description' => $this->str->translate('This URL field appears when "Type A" is chosen.'),
                'dependency' => [
                    'parent_field' => 'parent_radio_field',
                    'parent_value' => 'typeA',
                    'type' => 'radio',
                ],
            ]))
            ->addOption(new UploaderAbstractFieldDto('dependent_uploader_field', [
                'label' => $this->str->translate('Uploader for Type B'),
                'description' => $this->str->translate('This uploader appears when "Type B" is chosen.'),
                'dependency' => [
                    'parent_field' => 'parent_radio_field',
                    'parent_value' => 'typeB',
                    'type' => 'radio',
                ],
            ]))
            //-- Section
            ->addSection('appearance', $this->str->translate('Appearance'))
            ->addBlock('account_theme', $this->str->translate('Account Theme'))
            ->addOption(new SelectAbstractFieldDto('account_theme', [
                'label' => $this->str->translate('Select Theme'),
                'options' => $this->themeManager->discoverThemes(),
            ]));

        return $this->hookManager->applyFilters('usp_settings_config', $config);
    }

    private function getPagesAsOptions(): array
    {
        $pages = get_pages();
        $options = ['' => $this->str->translate('— Select a page —')];
        foreach ($pages as $page) {
            $options[$page->ID] = $page->post_title;
        }
        return $options;
    }
}