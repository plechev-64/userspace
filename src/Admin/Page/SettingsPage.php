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
use UserSpace\Core\Theme\ThemeManager;

/**
 * Управляет главной страницей настроек плагина.
 */
class SettingsPage extends AbstractAdminPage
{
    private const OPTION_GROUP = 'usp_settings_group';
    private const OPTION_NAME = 'usp_settings';

    public function __construct(
        private readonly FormFactory    $formFactory,
        private readonly ThemeManager   $themeManager,
        private readonly SettingsConfig $settingsConfig
    )
    {
    }

    protected function getPageTitle(): string
    {
        return __('UserSpace Settings', 'usp');
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
     * Регистрирует настройки для сохранения через Settings API.
     */
    public function registerSettings(): void
    {
        register_setting(self::OPTION_GROUP, self::OPTION_NAME);
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

        wp_enqueue_script('usp-uploader-handler');

        wp_enqueue_style('usp-form-style', USERSPACE_PLUGIN_URL . 'assets/css/form.css', [], USERSPACE_VERSION);

        wp_enqueue_style(
            'usp-admin-settings',
            USERSPACE_PLUGIN_URL . 'assets/css/admin-settings.css',
            [],
            USERSPACE_VERSION
        );

        wp_enqueue_script(
            'usp-admin-settings-js',
            USERSPACE_PLUGIN_URL . 'assets/js/admin-settings.js',
            [],
            USERSPACE_VERSION,
            true
        );

        wp_localize_script(
            'usp-admin-settings-js',
            'uspApiSettings',
            [
                'root' => esc_url_raw(rest_url()),
                'namespace' => USERSPACE_REST_NAMESPACE,
                'nonce' => wp_create_nonce('wp_rest'),
            ]
        );

        wp_localize_script(
            'usp-admin-settings-js',
            'uspL10n',
            [
                'adminSettings' => [
                    'saving' => __('Saving...', 'usp'),
                    'networkError' => __('Network error occurred.', 'usp'),
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
        $options = get_option(self::OPTION_NAME, []);

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
        echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';

        echo '<div id="usp-settings-notifications"></div>';

        echo '<div class="usp-settings-layout">';

        // Меню табов
        echo '<ul class="usp-settings-tabs-menu">';
        foreach ($formConfig->toArray()['sections'] as $index => $section) {
            $id = $section['id'] ?? 'section-' . $index;
            $class = $index === 0 ? 'active' : '';
            echo '<li><a href="#' . esc_attr($id) . '" class="' . esc_attr($class) . '">' . esc_html($section['title']) . '</a></li>';
        }
        echo '</ul>';

        // Контент табов
        echo '<div class="usp-settings-tabs-content">';
        echo '<div id="usp-settings-form-wrapper">'; // Обертка вместо <form>

        $allSections = $formConfig->toArray()['sections'];
        foreach ($allSections as $index => $section) {
            $id = $section['id'] ?? 'section-' . $index;
            $class = $index === 0 ? 'active' : '';
            echo '<div id="' . esc_attr($id) . '" class="usp-tab-pane ' . esc_attr($class) . '">';

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

        echo '<p class="submit"><button type="button" id="usp-save-settings" class="button button-primary">' . __('Save Settings', 'usp') . '</button></p>';

        echo '</div>'; // .usp-settings-tabs-content

        echo '</div>'; // .usp-settings-layout
        echo '</div>'; // .wrap
    }

    /**
     * Собирает конфигурацию для формы настроек через фильтр.
     * @return SettingsConfig
     */
    private function getSettingsConfig(): SettingsConfig
    {
        $config = $this->settingsConfig
            //-- Section
            ->addSection('general', __('General', 'usp'))
            ->addBlock('main', __('Main Settings', 'usp'))
            ->addOption(new TextFieldDto('api_key', ['label' => __('API Key', 'usp')]))
            ->addOption(new BooleanFieldDto('enable_feature_x', ['label' => __('Enable Feature X', 'usp')]))
            ->addOption(new UploaderFieldDto('default_avatar_id', [
                'label' => __('Default Avatar', 'usp'),
                'allowed_types' => 'image/jpeg',
                'image_max_width' => 500,
            ]))
            ->addOption(new UploaderFieldDto('files', [
                'label' => __('Files', 'usp'),
                'allowed_types' => 'image/jpeg',
                'multiply' => true,
            ]))
            ->addOption(new BooleanFieldDto('enable_user_bar', ['label' => __('Enable User Bar at the top of the site', 'usp')]))
            ->addOption(new BooleanFieldDto('require_email_confirmation', ['label' => __('Require email confirmation for registration', 'usp')]))
            ->addOption(new CheckboxFieldDto('prefer_color', [
                'label' => __('Prefer color', 'usp'),
                'options' => [
                    'white' => __('White', 'usp'),
                    'black' => __('Black', 'usp'),
                    'green' => __('Green', 'usp')
                ],
            ]))
            //-- Section
            ->addSection('advanced', __('Advanced', 'usp'))
            ->addBlock('integration', __('Integration', 'usp'))
            ->addOption(new SelectFieldDto('integration_mode', [
                'label' => __('Integration Mode', 'usp'),
                'options' => ['mode1' => __('Mode 1', 'usp'), 'mode2' => __('Mode 2', 'usp')],
            ]))
            ->addOption(new UrlFieldDto('webhook_url', ['label' => __('Webhook URL', 'usp')]))
            ->addBlock('other', __('Other', 'usp'))
            ->addOption(new RadioFieldDto('user_role', [
                'label' => __('Default Role', 'usp'),
                'options' => ['subscriber' => __('Subscriber', 'usp'), 'editor' => __('Editor', 'usp')],
            ]))
            ->addOption(new TextareaFieldDto('custom_css', ['label' => __('Custom CSS', 'usp')]))
            //-- Section
            ->addSection('page_settings', __('Page Assignment', 'usp'))
            ->addBlock('core_pages', __('Core Pages', 'usp'))
            ->addOption(new SelectFieldDto('login_page_id', [
                'label' => __('Login Page', 'usp'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectFieldDto('registration_page_id', [
                'label' => __('Registration Page', 'usp'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectFieldDto('password_reset_page_id', [
                'label' => __('Password Recovery Page', 'usp'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectFieldDto('profile_page_id', [
                'label' => __('User Profile Page', 'usp'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new TextFieldDto('profile_user_query_var', [
                'label' => __('User ID Query Variable', 'usp'),
                'description' => __('The GET parameter in the URL to identify the user. Default: <code>user_id</code>.', 'usp'),
            ]))
            //-- Section
            ->addSection('appearance', __('Appearance', 'usp'))
            ->addBlock('account_theme', __('Account Theme', 'usp'))
            ->addOption(new SelectFieldDto('account_theme', [
                'label' => __('Select Theme', 'usp'),
                'options' => $this->themeManager->discoverThemes(),
            ]));

        return apply_filters('usp_settings_config', $config);
    }

    private function getPagesAsOptions(): array
    {
        $pages = get_pages();
        $options = ['' => __('— Select a page —', 'usp')];
        foreach ($pages as $page) {
            $options[$page->ID] = $page->post_title;
        }
        return $options;
    }
}