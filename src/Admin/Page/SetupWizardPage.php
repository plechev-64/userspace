<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Core\SetupWizard\SetupWizardConfig;
use UserSpace\Form\Field\DTO\SelectFieldDto;
use UserSpace\Form\FormConfig;
use UserSpace\Form\FormFactory;

/**
 * Управляет страницей пошаговой настройки плагина.
 */
class SetupWizardPage extends AbstractAdminPage
{
    private const OPTION_NAME = 'usp_settings';

    public function __construct(
        private readonly FormFactory       $formFactory,
        private readonly SetupWizardConfig $wizardConfig
    )
    {
    }

    public function addPage(): void
    {
        $this->hookSuffix = add_submenu_page(
            null, // Не добавляем в основное меню, страница будет скрытой
            $this->getPageTitle(),
            $this->getMenuTitle(),
            $this->getCapability(),
            $this->getMenuSlug(),
            [$this, 'render'],
            $this->getPosition()
        );
    }

    public function enqueueAssets(string $hook): void
    {
        if ($this->hookSuffix !== $hook) {
            return;
        }

        wp_enqueue_style(
            'usp-form-style',
            USERSPACE_PLUGIN_URL .
            'assets/css/form.css',
            [],
            USERSPACE_VERSION
        );

        wp_enqueue_style(
            'usp-setup-wizard',
            USERSPACE_PLUGIN_URL . 'assets/css/setup-wizard.css',
            [],
            USERSPACE_VERSION
        );

        // Подключаем основной скрипт core.js, если он еще не подключен
        wp_enqueue_script('usp-core-js',
            USERSPACE_PLUGIN_URL . 'assets/js/core.js',
            [],
            USERSPACE_VERSION,
            true
        );

        wp_enqueue_script(
            'usp-setup-wizard-js',
            USERSPACE_PLUGIN_URL . 'assets/js/setup-wizard.js',
            ['usp-core-js'],
            USERSPACE_VERSION,
            true
        );

        wp_localize_script(
            'usp-core-js', // Локализация теперь привязывается к core.js
            'uspApiSettings',
            [
                'root' => esc_url_raw(rest_url()),
                'namespace' => USERSPACE_REST_NAMESPACE,
                'nonce' => wp_create_nonce('wp_rest'),
            ]
        );

        wp_localize_script(
            'usp-setup-wizard-js',
            'uspL10n',
            [
                'wizard' => [
                    'saving' => __('Saving...', 'usp'),
                    'networkError' => __('Network error occurred.', 'usp'),
                    'finish' => __('Finish', 'usp'),
                    'next' => __('Next', 'usp'),
                ],
            ]
        );
    }

    public function render(): void
    {
        $wizardConfig = $this->getWizardConfig();
        $config = $wizardConfig->toArray();
        $options = get_option(self::OPTION_NAME, []);

        echo '<div class="wrap usp-setup-wizard-wrap">';
        echo '<h1>' . esc_html($this->getPageTitle()) . '</h1>';

        echo '<div id="usp-wizard-notifications"></div>';

        // Навигация по шагам
        echo '<div class="usp-wizard-steps">';
        foreach ($config['steps'] as $index => $step) {
            $class = $index === 0 ? 'active' : '';
            echo '<div class="usp-wizard-step ' . esc_attr($class) . '" data-step="' . esc_attr($index) . '">';
            echo '<div class="usp-wizard-step-number">' . ($index + 1) . '</div>';
            echo '<div class="usp-wizard-step-title">' . esc_html($step['title']) . '</div>';
            echo '</div>';
        }
        echo '</div>';

        // Контент шагов
        echo '<div class="usp-wizard-content">';
        echo '<div id="usp-wizard-form" class="usp-form">'; // Обертка вместо <form>

        foreach ($config['steps'] as $index => $step) {
            $class = $index === 0 ? 'active' : '';
            echo '<div class="usp-wizard-pane ' . esc_attr($class) . '" data-step-content="' . esc_attr($index) . '">';

            if ($index === 0) {
                echo '<p class="usp-wizard-intro">' . __('Welcome! This wizard will help you with the initial setup of the plugin. Please follow the steps to configure the most important options.', 'usp') . '</p>';
            } elseif ($index === count($config['steps']) - 1) {
                echo '<p class="usp-wizard-intro">' . __('Setup is complete! You have configured the basic settings. You can change them at any time on the plugin settings page.', 'usp') . '</p>';
            }

            $stepFormConfig = new FormConfig();
            // FormConfig требует, чтобы поля находились внутри блоков,
            // а блоки - внутри секций.
            $stepFormConfig->addSection('');
            $stepFormConfig->addBlock('');
            foreach ($step['fields'] as $name => $fieldData) {
                if (isset($options[$name])) {
                    $fieldData['value'] = $options[$name];
                }
                $stepFormConfig->addField($name, $fieldData);
            }

            $stepForm = $this->formFactory->create($stepFormConfig);
            echo $stepForm->render();

            echo '</div>';
        }

        echo '</div>'; // #usp-wizard-form
        echo '</div>'; // .usp-wizard-content

        // Кнопки управления
        echo '<div class="usp-wizard-actions">';
        echo '<button type="button" id="usp-wizard-prev" class="button" style="display: none;">' . __('Previous', 'usp') . '</button>';
        echo '<button type="button" id="usp-wizard-next" class="button button-primary">' . __('Next', 'usp') . '</button>';
        echo '<a href="' . esc_url(admin_url('admin.php?page=userspace-settings')) . '" id="usp-wizard-finish" class="button button-primary" style="display: none;">' . __('Go to Settings', 'usp') . '</a>';
        echo '</div>';

        echo '</div>'; // .wrap
    }

    protected function getPageTitle(): string
    {
        return __('UserSpace Setup Wizard', 'usp');
    }

    protected function getMenuTitle(): string
    {
        return __('Setup Wizard', 'usp');
    }

    protected function getMenuSlug(): string
    {
        return 'userspace-setup';
    }

    /**
     * Собирает конфигурацию для мастера настройки.
     * @return SetupWizardConfig
     */
    private function getWizardConfig(): SetupWizardConfig
    {
        $config = $this->wizardConfig
            //-- Шаг 1: Основные страницы
            ->addStep('pages', __('Page Assignment', 'usp'))
            ->addOption(new SelectFieldDto('login_page_id', [
                'label' => __('Login Page', 'usp'),
                'options' => $this->getPagesAsOptions(),
                'description' => __('Select the page where the login form will be displayed.', 'usp'),
            ]))
            ->addOption(new SelectFieldDto('registration_page_id', [
                'label' => __('Registration Page', 'usp'),
                'options' => $this->getPagesAsOptions(),
                'description' => __('Select the page for the user registration form.', 'usp'),
            ]))
            ->addOption(new SelectFieldDto('profile_page_id', [
                'label' => __('User Profile Page', 'usp'),
                'options' => $this->getPagesAsOptions(),
                'description' => __('Select the page to display user profiles.', 'usp'),
            ]))

            //-- Шаг 2: Дополнительные настройки
            ->addStep('advanced', __('Advanced Settings', 'usp'))
            ->addOption(new SelectFieldDto('password_reset_page_id', [
                'label' => __('Password Recovery Page', 'usp'),
                'options' => $this->getPagesAsOptions(),
            ]));

        return apply_filters('usp_setup_wizard_config', $config);
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