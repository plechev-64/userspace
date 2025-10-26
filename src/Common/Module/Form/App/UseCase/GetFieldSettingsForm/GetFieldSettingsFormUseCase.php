<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm;

use UserSpace\Common\Module\Form\Src\Domain\Factory\FieldFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Service\FieldMapRegistryInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Factory\FormFactory;

class GetFieldSettingsFormUseCase
{
    public function __construct(
        private readonly FieldFactoryInterface     $fieldFactory,
        private readonly FieldMapRegistryInterface $fieldMapper,
        private readonly FormFactory               $formFactory
    )
    {
    }

    /**
     */
    public function execute(GetFieldSettingsFormCommand $command): GetFieldSettingsFormResult
    {
        $fieldDto = $command->fieldDto;

        $field = $this->fieldFactory->createFromDto($fieldDto);
        $settingsFields = $field->getSettingsFormConfig();

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

        // Возвращаем объект-результат с готовой формой, а не HTML
        return new GetFieldSettingsFormResult($settingsForm);
    }
}