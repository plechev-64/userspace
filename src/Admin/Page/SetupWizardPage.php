<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Page\Abstract\AbstractAdminPage;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\Factory\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectFieldDto;
use UserSpace\Common\Module\Settings\Src\Domain\PluginSettingsInterface;
use UserSpace\Common\Module\SetupWizard\Domain\SetupWizardConfig;
use UserSpace\Core\Admin\AdminApiInterface;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Управляет страницей пошаговой настройки плагина.
 */
class SetupWizardPage extends AbstractAdminPage
{
    public function __construct(
        private readonly FormFactory             $formFactory,
        private readonly SetupWizardConfig       $wizardConfig,
        private readonly StringFilterInterface   $str,
        private readonly AssetRegistryInterface  $assetRegistry,
        private readonly PluginSettingsInterface $pluginSettings,
        AdminApiInterface                        $adminApi,
        HookManagerInterface                     $hookManager
    )
    {
        parent::__construct($adminApi, $hookManager);
    }

    public function addPage(): void
    {
        $this->hookSuffix = $this->adminApi->addSubmenuPage(
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

        $this->assetRegistry->enqueueStyle(
            'usp-form-style',
            USERSPACE_PLUGIN_URL .
            'assets/css/form.css',
            [],
            USERSPACE_VERSION
        );

        $this->assetRegistry->enqueueStyle(
            'usp-setup-wizard',
            USERSPACE_PLUGIN_URL . 'assets/css/setup-wizard.css',
            [],
            USERSPACE_VERSION
        );

        $this->assetRegistry->enqueueScript(
            'usp-setup-wizard-js',
            USERSPACE_PLUGIN_URL . 'assets/js/setup-wizard.js',
            ['usp-core'],
            USERSPACE_VERSION,
            true
        );

        $this->assetRegistry->localizeScript(
            'usp-setup-wizard-js',
            'uspL10n',
            [
                'wizard' => [
                    'saving' => $this->str->translate('Saving...'),
                    'networkError' => $this->str->translate('Network error occurred.'),
                    'finish' => $this->str->translate('Finish'),
                    'next' => $this->str->translate('Next'),
                ],
            ]
        );
    }

    public function render(): void
    {
        $wizardConfig = $this->getWizardConfig();
        $config = $wizardConfig->toArray();
        $options = $this->pluginSettings->all();

        echo '<div class="wrap usp-setup-wizard-wrap">';
        echo '<h1>' . $this->str->escHtml($this->adminApi->getAdminPageTitle()) . '</h1>';

        echo '<div id="usp-wizard-notifications"></div>';

        // Навигация по шагам
        echo '<div class="usp-wizard-steps">';
        foreach ($config['steps'] as $index => $step) {
            $class = $index === 0 ? 'active' : '';
            echo '<div class="usp-wizard-step ' . $this->str->escAttr($class) . '" data-step="' . $this->str->escAttr($index) . '">';
            echo '<div class="usp-wizard-step-number">' . ($index + 1) . '</div>';
            echo '<div class="usp-wizard-step-title">' . $this->str->escHtml($step['title']) . '</div>';
            echo '</div>';
        }
        echo '</div>';

        // Контент шагов
        echo '<div class="usp-wizard-content">';
        echo '<div id="usp-wizard-form" class="usp-form">'; // Обертка вместо <form>

        foreach ($config['steps'] as $index => $step) {
            $class = $index === 0 ? 'active' : '';
            echo '<div class="usp-wizard-pane ' . $this->str->escAttr($class) . '" data-step-content="' . $this->str->escAttr($index) . '">';

            if ($index === 0) {
                echo '<p class="usp-wizard-intro">' . $this->str->translate('Welcome! This wizard will help you with the initial setup of the plugin. Please follow the steps to configure the most important options.') . '</p>';
            } elseif ($index === count($config['steps']) - 1) {
                echo '<p class="usp-wizard-intro">' . $this->str->translate('Setup is complete! You have configured the basic settings. You can change them at any time on the plugin settings page.') . '</p>';
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
        echo '<button type="button" id="usp-wizard-prev" class="button" style="display: none;">' . $this->str->translate('Previous') . '</button>';
        echo '<button type="button" id="usp-wizard-next" class="button button-primary">' . $this->str->translate('Next') . '</button>';;
        echo '<a href="' . $this->str->escUrl($this->adminApi->adminUrl('admin.php?page=userspace-settings')) . '" id="usp-wizard-finish" class="button button-primary" style="display: none;">' . $this->str->translate('Go to Settings') . '</a>';
        echo '</div>';

        echo '</div>'; // .wrap
    }

    protected function getPageTitle(): string
    {
        return $this->str->translate('UserSpace Setup Wizard');
    }

    protected function getMenuTitle(): string
    {
        return $this->str->translate('Setup Wizard');
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
            ->addStep('pages', $this->str->translate('Page Assignment'))
            ->addOption(new SelectFieldDto('login_page_id', [
                'label' => $this->str->translate('Login Page'),
                'options' => $this->getPagesAsOptions(),
                'description' => $this->str->translate('Select the page where the login form will be displayed.'),
            ]))
            ->addOption(new SelectFieldDto('registration_page_id', [
                'label' => $this->str->translate('Registration Page'),
                'options' => $this->getPagesAsOptions(),
                'description' => $this->str->translate('Select the page for the user registration form.'),
            ]))
            ->addOption(new SelectFieldDto('profile_page_id', [
                'label' => $this->str->translate('User Profile Page'),
                'options' => $this->getPagesAsOptions(),
                'description' => $this->str->translate('Select the page to display user profiles.'),
            ]))

            //-- Шаг 2: Дополнительные настройки
            ->addStep('advanced', $this->str->translate('Advanced Settings'))
            ->addOption(new SelectFieldDto('password_reset_page_id', [
                'label' => $this->str->translate('Password Recovery Page'),
                'options' => $this->getPagesAsOptions(),
            ]));

        return $this->hookManager->applyFilters('usp_setup_wizard_config', $config);
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