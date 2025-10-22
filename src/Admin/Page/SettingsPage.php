<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Admin\SettingsConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\BooleanFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\CheckboxFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\RadioFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextareaFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UploaderFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UrlFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Core\Admin\AdminApiInterface;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\Theme\ThemeManager;

/**
 * Управляет главной страницей настроек плагина.
 */
class SettingsPage extends AbstractAdminPage
{
	private const OPTION_GROUP = 'usp_settings_group';
	private const OPTION_NAME = 'usp_settings';

	public function __construct(
        private readonly FormFactory            $formFactory,
        private readonly ThemeManager           $themeManager,
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
            echo $sectionForm->render();
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
            ->addOption(new TextFieldDto('api_key', ['label' => $this->str->translate('API Key')]))
            ->addOption(new BooleanFieldDto('enable_feature_x', ['label' => $this->str->translate('Enable Feature X')]))
            ->addOption(new UploaderFieldDto('default_avatar_id', [
                'label' => $this->str->translate('Default Avatar'),
                'allowed_types' => 'image/jpeg',
                'image_max_width' => 500,
            ]))
            ->addOption(new UploaderFieldDto('files', [
                'label' => $this->str->translate('Files'),
                'allowed_types' => 'image/jpeg',
                'multiply' => true,
            ]))
            ->addOption(new BooleanFieldDto('enable_user_bar', ['label' => $this->str->translate('Enable User Bar at the top of the site')]))
            ->addOption(new BooleanFieldDto('require_email_confirmation', ['label' => $this->str->translate('Require email confirmation for registration')]))
            ->addOption(new CheckboxFieldDto('prefer_color', [
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
            ->addOption(new SelectFieldDto('integration_mode', [
                'label' => $this->str->translate('Integration Mode'),
                'options' => ['mode1' => $this->str->translate('Mode 1'), 'mode2' => $this->str->translate('Mode 2')],
            ]))
            ->addOption(new UrlFieldDto('webhook_url', ['label' => $this->str->translate('Webhook URL')]))
            ->addBlock('other', $this->str->translate('Other'))
            ->addOption(new RadioFieldDto('user_role', [
                'label' => $this->str->translate('Default Role'),
                'options' => ['subscriber' => $this->str->translate('Subscriber'), 'editor' => $this->str->translate('Editor')],
            ]))
            ->addOption(new TextareaFieldDto('custom_css', ['label' => $this->str->translate('Custom CSS')]))
            //-- Section
            ->addSection('page_settings', $this->str->translate('Page Assignment'))
            ->addBlock('core_pages', $this->str->translate('Core Pages'))
            ->addOption(new SelectFieldDto('login_page_id', [
                'label' => $this->str->translate('Login Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectFieldDto('registration_page_id', [
                'label' => $this->str->translate('Registration Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectFieldDto('password_reset_page_id', [
                'label' => $this->str->translate('Password Recovery Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectFieldDto('profile_page_id', [
                'label' => $this->str->translate('User Profile Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new TextFieldDto('profile_user_query_var', [
                'label' => $this->str->translate('User ID Query Variable'),
                'description' => $this->str->translate('The GET parameter in the URL to identify the user. Default: <code>user_id</code>.'),
            ]))
            ->addOption(new TextFieldDto('profile_tab_query_var', [
                'label' => $this->str->translate('Profile Tab Query Variable'),
                'description' => $this->str->translate('The GET parameter in the URL to identify the profile tab. Default: <code>tab</code>.'),
            ]))
            //-- Section
            ->addSection('appearance', $this->str->translate('Appearance'))
            ->addBlock('account_theme', $this->str->translate('Account Theme'))
            ->addOption(new SelectFieldDto('account_theme', [
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