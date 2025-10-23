<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm;

use UserSpace\Common\Module\Form\Src\Infrastructure\FieldMapper;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;

class GetFieldSettingsFormUseCase
{
    public function __construct(
        private readonly FieldMapper $fieldMapper,
        private readonly FormFactory $formFactory
    )
    {
    }

    /**
     */
    public function execute(GetFieldSettingsFormCommand $command): GetFieldSettingsFormResult
    {
        $fieldDto = $command->fieldDto;

        $fieldClass = $this->fieldMapper->getClass($fieldDto->type);
        $settingsFields = $fieldClass::getSettingsFormConfig();

        // Заполняем поля формы текущими значениями из конфигурации поля
        // Теперь мы можем напрямую обращаться к свойствам DTO
        foreach ($settingsFields as $settingName => &$settingConfig) {
            if ($settingName === 'required') {
                $settingConfig['value'] = !empty($fieldDto->rules['required']);
            } elseif (property_exists($fieldDto, $settingName)) {
                $settingConfig['value'] = $fieldDto->$settingName;
            } else if (isset($fieldDto->attributes[$settingName])) {
                $settingConfig['value'] = $fieldDto->attributes[$settingName];
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