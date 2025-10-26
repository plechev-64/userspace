<?php

namespace UserSpace\Common\Module\Locations\App\UseCase\GetSettingsForm;

use UserSpace\Common\Module\Form\Src\Infrastructure\Factory\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\FormConfig;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\String\StringFilterInterface;

class GetItemSettingsFormUseCase
{
    public function __construct(
        private readonly FormFactory           $formFactory,
        private readonly StringFilterInterface $str,
        private readonly SanitizerInterface    $sanitizer
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(GetItemSettingsFormCommand $command): GetItemSettingsFormResult
    {
        $decodedConfig = json_decode($command->tabConfigJson, true);

        if (!is_array($decodedConfig)) {
            throw new UspException($this->str->translate('Invalid tab configuration format.'), 400);
        }

        // Очищаем данные, полученные из JSON
        $sanitizedConfig = $this->sanitizer->sanitize($decodedConfig, [
            'title' => SanitizerRule::TEXT_FIELD,
            'icon' => SanitizerRule::TEXT_FIELD,
            'capability' => SanitizerRule::KEY,
            'isPrivate' => SanitizerRule::BOOL,
            'isDefault' => SanitizerRule::BOOL,
        ])->all();

        $settingsFields = $this->getSettingsFields();

        // Заполняем поля формы текущими значениями из конфигурации вкладки
        if (!empty($sanitizedConfig)) {
            foreach ($settingsFields as $settingName => &$settingConfig) {
                if (isset($sanitizedConfig[$settingName])) {
                    $settingConfig['value'] = $sanitizedConfig[$settingName];
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

        return new GetItemSettingsFormResult($settingsForm);
    }

    /**
     * Определяет поля для формы настроек вкладки.
     * @return array<string, array<string, mixed>>
     */
    private function getSettingsFields(): array
    {
        /** @todo вынести формирование настроек в каждый отдельный ItemInterface */
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