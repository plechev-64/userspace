<?php

namespace UserSpace\Admin\Service;

use UserSpace\Admin\SettingsConfig;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\BooleanAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\CheckboxAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\RadioAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextareaAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UploaderAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UrlAbstractFieldDto;
use UserSpace\Common\Module\Settings\App\SettingsEnum;
use UserSpace\Common\Module\Settings\Src\Domain\PluginSettingsInterface;
use UserSpace\Core\Addon\Theme\ThemeManagerInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;

class SettingsFormConfigService implements SettingsFormConfigServiceInterface
{
    public function __construct(
        private readonly SettingsConfig          $settingsConfig,
        private readonly PluginSettingsInterface $pluginSettings,
        private readonly StringFilterInterface   $str,
        private readonly ThemeManagerInterface   $themeManager,
        private readonly HookManagerInterface    $hookManager
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getFormConfig(): FormConfig
    {
        $config = $this->getSettingsConfig();

        $formConfig = new FormConfig();

        $options = $this->pluginSettings->all();
        $configArray = $config->toArray();

        // Проверяем, что в конфигурации есть секции.
        if (empty($configArray['sections']) || !is_array($configArray['sections'])) {
            return $formConfig;
        }

        foreach ($configArray['sections'] as $section) {
            $formConfig->addSection($section['title']);

            foreach ($section['blocks'] ?? [] as $block) {
                $formConfig->addBlock($block['title']);

                foreach ($block['fields'] ?? [] as $name => $fieldData) {
                    // Применяем сохраненное значение, если оно существует.
                    $fieldData['value'] = $options[$name] ?? $fieldData['value'] ?? null;
                    $formConfig->addField($name, $fieldData);
                }
            }
        }

        return $formConfig;
    }

    /**
     * Собирает конфигурацию для формы настроек через фильтр.
     */
    public function getSettingsConfig(): SettingsConfig
    {
        $config = $this->settingsConfig
            //-- Section
            ->addSection('general', $this->str->translate('General'))
            ->addBlock('main', $this->str->translate('Main Settings')) // Блок
            ->addOption(new TextAbstractFieldDto(SettingsEnum::API_KEY->value, ['label' => $this->str->translate('API Key')]))
            ->addOption(new BooleanAbstractFieldDto(SettingsEnum::ENABLE_FEATURE_X->value, ['label' => $this->str->translate('Enable Feature X')]))
            ->addOption(new UploaderAbstractFieldDto(SettingsEnum::DEFAULT_AVATAR_ID->value, [
                'label' => $this->str->translate('Default Avatar'),
                'allowed_types' => 'image/jpeg',
                'image_max_width' => 500,
            ]))
            ->addOption(new UploaderAbstractFieldDto(SettingsEnum::FILES->value, [
                'label' => $this->str->translate('Files'),
                'allowed_types' => 'image/jpeg',
                'multiple' => true,
            ]))
            ->addOption(new BooleanAbstractFieldDto(SettingsEnum::ENABLE_USER_BAR->value, ['label' => $this->str->translate('Enable User Bar at the top of the site')]))
            ->addOption(new BooleanAbstractFieldDto(SettingsEnum::REQUIRE_EMAIL_CONFIRMATION->value, ['label' => $this->str->translate('Require email confirmation for registration')]))
            ->addOption(new CheckboxAbstractFieldDto(SettingsEnum::PREFER_COLOR->value, [
                'label' => $this->str->translate('Prefer color'),
                'options' => [
                    'white' => $this->str->translate('White'),
                    'black' => $this->str->translate('Black'),
                    'green' => $this->str->translate('Green')
                ],
            ]))
            //-- Section
            ->addSection('advanced', $this->str->translate('Advanced'))
            ->addBlock('integration', $this->str->translate('Integration')) // Блок
            ->addOption(new SelectAbstractFieldDto(SettingsEnum::INTEGRATION_MODE->value, [
                'label' => $this->str->translate('Integration Mode'),
                'options' => ['mode1' => $this->str->translate('Mode 1'), 'mode2' => $this->str->translate('Mode 2')],
            ]))
            ->addOption(new UrlAbstractFieldDto(SettingsEnum::WEBHOOK_URL->value, ['label' => $this->str->translate('Webhook URL')]))
            ->addBlock('other', $this->str->translate('Other')) // Блок
            ->addOption(new RadioAbstractFieldDto(SettingsEnum::USER_ROLE->value, [
                'label' => $this->str->translate('Default Role'),
                'options' => ['subscriber' => $this->str->translate('Subscriber'), 'editor' => $this->str->translate('Editor')],
            ]))
            ->addOption(new TextareaAbstractFieldDto(SettingsEnum::CUSTOM_CSS->value, ['label' => $this->str->translate('Custom CSS')]))
            //-- Section
            ->addSection('page_settings', $this->str->translate('Page Assignment'))
            ->addBlock('core_pages', $this->str->translate('Core Pages')) // Блок
            ->addOption(new SelectAbstractFieldDto(SettingsEnum::LOGIN_PAGE_ID->value, [
                'label' => $this->str->translate('Login Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectAbstractFieldDto(SettingsEnum::REGISTRATION_PAGE_ID->value, [
                'label' => $this->str->translate('Registration Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectAbstractFieldDto(SettingsEnum::REDIRECT_AFTER_LOGIN_PAGE_ID->value, [
                'label' => $this->str->translate('Redirect After Login Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectAbstractFieldDto(SettingsEnum::PASSWORD_RESET_PAGE_ID->value, [
                'label' => $this->str->translate('Password Recovery Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new SelectAbstractFieldDto(SettingsEnum::PROFILE_PAGE_ID->value, [
                'label' => $this->str->translate('User Profile Page'),
                'options' => $this->getPagesAsOptions(),
            ]))
            ->addOption(new TextAbstractFieldDto(SettingsEnum::PROFILE_USER_QUERY_VAR->value, [
                'label' => $this->str->translate('User ID Query Variable'),
                'description' => $this->str->translate('The GET parameter in the URL to identify the user. Default: <code>user_id</code>.'),
            ]))
            ->addOption(new TextAbstractFieldDto(SettingsEnum::PROFILE_TAB_QUERY_VAR->value, [
                'label' => $this->str->translate('Profile Tab Query Variable'),
                'description' => $this->str->translate('The GET parameter in the URL to identify the profile tab. Default: <code>tab</code>.'),
            ]))
            // NEW: Example Parent-Child Settings
            ->addSection('dependency_examples', $this->str->translate('Dependency Examples'))
            ->addBlock('parent_fields', $this->str->translate('Parent Fields')) // Блок
            ->addOption(new SelectAbstractFieldDto(SettingsEnum::PARENT_SELECT_FIELD->value, [
                'label' => $this->str->translate('Select an Option'),
                'options' => [
                    'option1' => $this->str->translate('Option 1 (shows text field)'),
                    'option2' => $this->str->translate('Option 2 (shows checkbox)'),
                    'option3' => $this->str->translate('Option 3 (shows radio)'),
                ],
                'description' => $this->str->translate('Select a value to reveal dependent fields.'),
            ]))
            ->addOption(new BooleanAbstractFieldDto(SettingsEnum::PARENT_CHECKBOX_FIELD->value, [
                'label' => $this->str->translate('Enable Feature Y'),
                'description' => $this->str->translate('Check this to reveal a dependent text area.'),
            ]))
            ->addOption(new RadioAbstractFieldDto(SettingsEnum::PARENT_RADIO_FIELD->value, [
                'label' => $this->str->translate('Choose a Type'),
                'options' => [
                    'typeA' => $this->str->translate('Type A (shows URL field)'),
                    'typeB' => $this->str->translate('Type B (shows uploader)'),
                ],
                'description' => $this->str->translate('Select a type to reveal dependent fields.'),
            ]))
            ->addBlock('dependent_fields', $this->str->translate('Dependent Fields')) // Блок
            ->addOption(new TextAbstractFieldDto(SettingsEnum::DEPENDENT_TEXT_FIELD->value, [
                'label' => $this->str->translate('Text Field for Option 1'),
                'description' => $this->str->translate('This field appears when "Option 1" is selected.'),
                'dependency' => [
                    'parent_field' => SettingsEnum::PARENT_SELECT_FIELD->value,
                    'parent_value' => 'option1',
                    'type' => 'select',
                ],
            ]))
            ->addOption(new CheckboxAbstractFieldDto(SettingsEnum::DEPENDENT_CHECKBOX_FIELD->value, [
                'label' => $this->str->translate('Checkbox for Option 2'),
                'description' => $this->str->translate('This checkbox appears when "Option 2" is selected.'),
                'options' => [
                    'checkA' => $this->str->translate('check A'),
                    'checkB' => $this->str->translate('check B'),
                ],
                'dependency' => [
                    'parent_field' => SettingsEnum::PARENT_SELECT_FIELD->value,
                    'parent_value' => 'option2',
                    'type' => 'select',
                ],
            ]))
            ->addOption(new RadioAbstractFieldDto(SettingsEnum::DEPENDENT_RADIO_FIELD->value, [
                'label' => $this->str->translate('Radio for Option 3'),
                'options' => [
                    'sub_option_a' => $this->str->translate('Sub Option A'),
                    'sub_option_b' => $this->str->translate('Sub Option B'),
                ],
                'description' => $this->str->translate('These radio buttons appear when "Option 3" is selected.'),
                'dependency' => [
                    'parent_field' => SettingsEnum::PARENT_SELECT_FIELD->value,
                    'parent_value' => 'option3',
                    'type' => 'select',
                ],
            ]))
            ->addOption(new TextareaAbstractFieldDto(SettingsEnum::DEPENDENT_TEXTAREA_FIELD->value, [
                'label' => $this->str->translate('Text Area for Feature Y'),
                'description' => $this->str->translate('This text area appears when "Enable Feature Y" is checked.'),
                'dependency' => [
                    'parent_field' => SettingsEnum::PARENT_CHECKBOX_FIELD->value,
                    'parent_value' => true, // Boolean for checkbox
                    'type' => 'checkbox',
                ],
            ]))
            ->addOption(new UrlAbstractFieldDto(SettingsEnum::DEPENDENT_URL_FIELD->value, [
                'label' => $this->str->translate('URL Field for Type A'),
                'description' => $this->str->translate('This URL field appears when "Type A" is chosen.'),
                'dependency' => [
                    'parent_field' => SettingsEnum::PARENT_RADIO_FIELD->value,
                    'parent_value' => 'typeA',
                    'type' => 'radio',
                ],
            ]))
            ->addOption(new UploaderAbstractFieldDto(SettingsEnum::DEPENDENT_UPLOADER_FIELD->value, [
                'label' => $this->str->translate('Uploader for Type B'),
                'description' => $this->str->translate('This uploader appears when "Type B" is chosen.'),
                'dependency' => [
                    'parent_field' => SettingsEnum::PARENT_RADIO_FIELD->value,
                    'parent_value' => 'typeB',
                    'type' => 'radio',
                ],
            ]))
            //-- Section
            ->addSection('appearance', $this->str->translate('Appearance'))
            ->addBlock('account_theme', $this->str->translate('Account Theme')) // Блок
            ->addOption(new SelectAbstractFieldDto(SettingsEnum::ACCOUNT_THEME->value, [
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