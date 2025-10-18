<?php

namespace UserSpace\Controller\Admin;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Form\FieldMapper;
use UserSpace\Form\FormFactory;

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

        $settingsFormConfig = ['sections' => [['blocks' => [['fields' => $settingsFields]]]]];
        $settingsForm = $this->formFactory->create($settingsFormConfig);

        return $this->success(['html' => '<form class="usp-form">' . $settingsForm->render() . '</form>']);
    }
}