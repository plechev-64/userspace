<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm;

use UserSpace\Common\Module\Form\Src\Infrastructure\FieldMapper;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class GetFieldSettingsFormUseCase
{
    public function __construct(
        private readonly FieldMapper           $fieldMapper,
        private readonly FormFactory           $formFactory,
        private readonly StringFilterInterface $str
    ) {
    }

    /**
     * @throws UspException
     */
    public function execute(GetFieldSettingsFormCommand $command): GetFieldSettingsFormResult
    {
        if (!$this->fieldMapper->has($command->fieldType)) {
            throw new UspException($this->str->translate('Invalid field type.'), 400);
        }

        $fieldConfig = json_decode($command->fieldConfigJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UspException($this->str->translate('Invalid field configuration format.'), 400);
        }

        $fieldClass = $this->fieldMapper->getClass($command->fieldType);
        $settingsFields = $fieldClass::getSettingsFormConfig();

        // Заполняем поля формы текущими значениями из конфигурации поля
        if (!empty($fieldConfig)) {
            foreach ($settingsFields as $settingName => &$settingConfig) {
                if ($settingName === 'required') {
                    $settingConfig['value'] = !empty($fieldConfig['rules']['required']);
                } elseif (isset($fieldConfig[$settingName])) {
                    $settingConfig['value'] = $fieldConfig[$settingName];
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

        return new GetFieldSettingsFormResult($html);
    }
}