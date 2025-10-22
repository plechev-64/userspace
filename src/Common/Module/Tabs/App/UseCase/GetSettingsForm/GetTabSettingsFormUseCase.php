<?php

namespace UserSpace\Common\Module\Tabs\App\UseCase\GetSettingsForm;

use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class GetTabSettingsFormUseCase
{
    public function __construct(
        private readonly FormFactory $formFactory,
        private readonly StringFilterInterface $str
    ) {
    }

    /**
     * @throws UspException
     */
    public function execute(GetTabSettingsFormCommand $command): GetTabSettingsFormResult
    {
        $tabConfig = json_decode($command->tabConfigJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UspException($this->str->translate('Invalid tab configuration format.'), 400);
        }

        $settingsFields = $this->getSettingsFields();

        // Заполняем поля формы текущими значениями из конфигурации вкладки
        if (!empty($tabConfig)) {
            foreach ($settingsFields as $settingName => &$settingConfig) {
                if (isset($tabConfig[$settingName])) {
                    $settingConfig['value'] = $tabConfig[$settingName];
                }
            }
        }

        $formConfig = new FormConfig();
        $formConfig->addSection(''); // Секция без заголовка
        $formConfig->addBlock('');   // Блок без заголовка

        foreach ($settingsFields as $name => $fieldData) {
            $formConfig->addField($name, $fieldData);
        }
        $settingsForm = $this->formFactory->create($formConfig);

        $html = '<form class="usp-form">' . $settingsForm->render() . '</form>';

        return new GetTabSettingsFormResult($html);
    }

    /**
     * Определяет поля для формы настроек вкладки.
     * @return array<string, array<string, mixed>>
     */
    private function getSettingsFields(): array
    {
        return [
            'title' => [
                'type' => 'text',
                'label' => $this->str->translate('Title'),
                'rules' => ['required' => true],
            ],
            'icon' => [
                'type' => 'text',
                'label' => $this->str->translate('Icon'),
                'description' => sprintf(
                /* translators: %s: link to Dashicons documentation */
                    $this->str->translate('Enter a Dashicon class name. See all available icons %s.'),
                    '<a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">' . $this->str->translate('here') . '</a>'
                ),
            ],
            'capability' => [
                'type' => 'text',
                'label' => $this->str->translate('Capability'),
                'description' => $this->str->translate('Required capability to view this tab (e.g., "read", "edit_posts").'),
                'value' => 'read', // Default value
            ],
            'isPrivate' => [
                'type' => 'boolean',
                'label' => $this->str->translate('Private Tab'),
                'description' => $this->str->translate('If checked, the tab will only be visible to the profile owner.'),
            ],
            'isDefault' => [
                'type' => 'boolean',
                'label' => $this->str->translate('Default Tab'),
                'description' => $this->str->translate('If checked, this tab will be opened by default.'),
            ],
            // Другие настройки, такие как contentType, contentSource, можно добавить здесь в будущем.
        ];
    }
}