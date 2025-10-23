<?php

namespace UserSpace\Common\Module\Form\App\Controller;

use UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm\GetFieldSettingsFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm\GetFieldSettingsFormUseCase;
use UserSpace\Common\Module\Form\App\UseCase\SaveConfig\SaveFormConfigCommand;
use UserSpace\Common\Module\Form\App\UseCase\SaveConfig\SaveProfileFormConfigUseCase;
use UserSpace\Common\Module\Form\App\UseCase\SaveConfig\SaveRegistrationFormConfigUseCase;
use UserSpace\Common\Module\Form\App\UseCase\SaveProfileForm\SaveProfileFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\SaveProfileForm\SaveProfileFormUseCase;
use UserSpace\Common\Module\Form\Src\Infrastructure\FieldMapper;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Renderer\ForgotPasswordFormRenderer;
use UserSpace\Common\Renderer\LoginFormRenderer;
use UserSpace\Common\Renderer\RegistrationFormRenderer;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/form')]
class FormController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface $str
    )
    {
    }

    #[Route(path: '/profile/save', method: 'POST')]
    public function saveProfile(Request $request, SaveProfileFormUseCase $saveProfileUseCase): JsonResponse
    {
        $command = new SaveProfileFormCommand('profile', $request->getPostParams());

        try {
            $saveProfileUseCase->execute($command);
            return $this->success(['message' => $this->str->translate('Data saved successfully!')]);
        } catch (UspException $e) {
            $errorData = ['message' => $e->getMessage()];
            if ($e->getCode() === 422 && !empty($e->getData()['errors'])) {
                $errorData['errors'] = $e->getData()['errors'];
            }
            return $this->error($errorData, $e->getCode());
        }
    }

    #[Route(path: '/modal/(?P<type>[a-zA-Z0-9_-]+)', method: 'GET')]
    public function getFormHtml(
        string                     $type,
        LoginFormRenderer          $loginFormRenderer,
        RegistrationFormRenderer   $registrationFormRenderer,
        ForgotPasswordFormRenderer $forgotPasswordFormRenderer
    ): JsonResponse
    {
        $renderer = match ($type) {
            'login' => $loginFormRenderer,
            'register' => $registrationFormRenderer,
            'forgot-password' => $forgotPasswordFormRenderer,
            default => null
        };

        if (!$renderer) {
            return $this->error(['message' => $this->str->translate('Invalid form type specified.')], 400);
        }

        $html = $renderer->render();

        return $this->success(['html' => $html]);
    }

    #[Route(path: '/field/settings', method: 'POST', permission: 'manage_options')]
    public function getFieldSettingsForm(
        Request                     $request,
        GetFieldSettingsFormUseCase $getFieldSettingsFormUseCase,
        FieldMapper                 $fieldMapper
    ): JsonResponse
    {
        $fieldType = $request->getPost('fieldType', '');
        $fieldConfig = json_decode($request->getPost('fieldConfig', '{}'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(['message' => $this->str->translate('Invalid field configuration format.')], 400);
        }

        $dtoClass = $fieldMapper->getDtoClass($fieldType);
        if (!$dtoClass) {
            return $this->error(['message' => $this->str->translate('Invalid field type specified.')], 400);
        }

        // Создаем DTO, используя имя из конфига или генерируя новое
        $fieldName = $fieldConfig['name'] ?? 'field_' . uniqid();
        $fieldDto = new $dtoClass($fieldName, $fieldConfig);

        $command = new GetFieldSettingsFormCommand($fieldDto);

        try {
            $result = $getFieldSettingsFormUseCase->execute($command);
            return $this->success(['html' => $result->html]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route(path: '/config/profile-form/save', method: 'POST', permission: 'manage_options')]
    final public function saveProfileConfig(Request $request, SaveProfileFormConfigUseCase $saveConfigUseCase): JsonResponse
    {
        $configArray = json_decode($request->getPost('config', '{}'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(['message' => $this->str->translate('Invalid JSON format.')], 400);
        }

        $deletedFields = json_decode($request->getPost('deleted_fields', '[]'), true) ?? [];

        $formConfig = FormConfig::fromArray($configArray);

        $command = new SaveFormConfigCommand($formConfig, $deletedFields);

        $saveConfigUseCase->execute($command);
        return $this->success(['message' => $this->str->translate('Configuration saved successfully.')]);
    }

    #[Route(path: '/config/registration-form/save', method: 'POST', permission: 'manage_options')]
    final public function saveRegistrationConfig(Request $request, SaveRegistrationFormConfigUseCase $saveRegistrationConfigUseCase): JsonResponse
    {
        $configArray = json_decode($request->getPost('config', '{}'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(['message' => $this->str->translate('Invalid JSON format.')], 400);
        }

        $deletedFields = json_decode($request->getPost('deleted_fields', '[]'), true) ?? [];

        $formConfig = FormConfig::fromArray($configArray);

        $command = new SaveFormConfigCommand($formConfig, $deletedFields);

        $saveRegistrationConfigUseCase->execute($command);
        return $this->success(['message' => $this->str->translate('Configuration saved successfully.')]);
    }
}