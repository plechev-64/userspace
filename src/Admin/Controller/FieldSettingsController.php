<?php

namespace UserSpace\Admin\Controller;

use UserSpace\Common\Module\Form\Src\Infrastructure\FieldMapper;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

#[Route(path: '/field-settings')]
class FieldSettingsController extends AbstractController
{
    public function __construct(
        private readonly FieldMapper $fieldMapper,
        private readonly FormFactory $formFactory
    ) {
    }

    #[Route(path: '/settings', method: 'POST', permission: 'manage_options')]
    public function getFieldSettingsForm(Request $request): JsonResponse
    {
        $fieldType = $request->getPost('fieldType', '');
        $fieldConfig = json_decode($request->getPost('fieldConfig', '{}'), true);

        if (!$this->fieldMapper->has($fieldType)) {
            return $this->error(['message' => 'Invalid field type.'], 400);
        }

        $fieldClass = $this->fieldMapper->getClass($fieldType);
        $settingsFields = $fieldClass::getSettingsFormConfig();

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

        return $this->success(['html' => '<form class="usp-form">' . $settingsForm->render() . '</form>']);
    }
}