<?php

namespace UserSpace\Common\Module\Form\App\Controller;

use UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm\GetFieldSettingsFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\GetFieldSettingsForm\GetFieldSettingsFormUseCase;
use UserSpace\Common\Module\Form\App\UseCase\SaveConfig\SaveFormConfigCommand;
use UserSpace\Common\Module\Form\App\UseCase\SaveConfig\SaveProfileFormConfigUseCase;
use UserSpace\Common\Module\Form\App\UseCase\SaveConfig\SaveRegistrationFormConfigUseCase;
use UserSpace\Common\Module\Form\App\UseCase\SaveProfileForm\SaveProfileFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\SaveProfileForm\SaveProfileFormUseCase;
use UserSpace\Common\Module\Form\Src\Domain\FieldMapperInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Renderer\ForgotPasswordFormRenderer;
use UserSpace\Common\Renderer\LoginFormRenderer;
use UserSpace\Common\Renderer\RegistrationFormRenderer;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/form')]
class FormController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface $str,
        private readonly SanitizerInterface    $sanitizer
    )
    {
    }

    #[Route(path: '/profile/save', method: 'POST')]
    public function saveProfile(Request $request, SaveProfileFormUseCase $saveProfileUseCase): JsonResponse
    {
        // Для сохранения профиля мы не можем применить строгую санитизацию ко всем полям,
        // так как некоторые могут содержать HTML. Валидация и санитизация происходят внутри UseCase.
        // Здесь мы просто передаем "сырые" данные.
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
        $clearedData = $this->sanitizer->sanitize(['type' => $type], ['type' => SanitizerRule::KEY]);
        $sanitizedType = $clearedData->get('type');

        if (empty($sanitizedType)) {
            return $this->error(['message' => $this->str->translate('Invalid form type specified.')], 400);
        }

        $renderer = match ($sanitizedType) {
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
        FieldMapperInterface                 $fieldMapper
    ): JsonResponse
    {
        $clearedData = $this->sanitizer->sanitize($request->getPostParams(), [
            'fieldType' => SanitizerRule::KEY,
            'fieldConfig' => SanitizerRule::TEXT_FIELD, // Санитизируем как текст, чтобы безопасно декодировать
        ]);

        $fieldType = $clearedData->get('fieldType', '');
        $fieldConfigJson = $clearedData->get('fieldConfig', '{}');

        $decodedConfig = json_decode($fieldConfigJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(['message' => $this->str->translate('Invalid field configuration format.')], 400);
        }

        // 2. Вторичная санитизация: очищаем данные *внутри* декодированного массива.
        $sanitizedConfig = $this->sanitizer->sanitize($decodedConfig, [
            'name' => SanitizerRule::KEY,
            'label' => SanitizerRule::TEXT_FIELD,
            'description' => SanitizerRule::KSES_POST, // Разрешаем безопасный HTML
            'placeholder' => SanitizerRule::TEXT_FIELD,
            'value' => SanitizerRule::TEXT_FIELD,
            'options' => SanitizerRule::TEXT_FIELD, // Очистит каждый элемент массива опций
            'rules' => SanitizerRule::TEXT_FIELD, // Очистит каждый элемент массива правил
        ])->all();

        $dtoClass = $fieldMapper->getDtoClass($fieldType);
        if (!$dtoClass) {
            return $this->error(['message' => $this->str->translate('Invalid field type specified.')], 400);
        }

        // Создаем DTO, используя имя из конфига или генерируя новое
        $fieldName = $sanitizedConfig['name'] ?? 'field_' . uniqid();
        $fieldDto = new $dtoClass($fieldName, $sanitizedConfig);

        $command = new GetFieldSettingsFormCommand($fieldDto);

        $result = $getFieldSettingsFormUseCase->execute($command);
        return $this->success(['html' => $result->html]);
    }

    #[Route(path: '/config/profile-form/save', method: 'POST', permission: 'manage_options')]
    final public function saveProfileConfig(Request $request, SaveProfileFormConfigUseCase $saveConfigUseCase): JsonResponse
    {
        return $this->handleSaveConfigRequest($request, $saveConfigUseCase);
    }

    #[Route(path: '/config/registration-form/save', method: 'POST', permission: 'manage_options')]
    final public function saveRegistrationConfig(Request $request, SaveRegistrationFormConfigUseCase $saveRegistrationConfigUseCase): JsonResponse
    {
        return $this->handleSaveConfigRequest($request, $saveRegistrationConfigUseCase);
    }

    /**
     * Общий обработчик для сохранения конфигураций форм.
     * @param Request $request
     * @param SaveProfileFormConfigUseCase|SaveRegistrationFormConfigUseCase $useCase
     * @return JsonResponse
     */
    private function handleSaveConfigRequest(Request $request, $useCase): JsonResponse
    {
        $clearedData = $this->sanitizer->sanitize($request->getPostParams(), [
            'config' => SanitizerRule::TEXT_FIELD,
            'deleted_fields' => SanitizerRule::TEXT_FIELD,
        ]);

        $decodedConfig = json_decode($clearedData->get('config', '{}'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(['message' => $this->str->translate('Invalid JSON format.')], 400);
        }

        // 2. Вторичная санитизация: очищаем данные *внутри* декодированного массива.
        // Здесь мы можем применить более общие правила, так как структура сложная.
        $sanitizedConfigArray = $this->sanitizer->sanitize($decodedConfig, [
            'sections' => SanitizerRule::TEXT_FIELD, // Рекурсивно очистит все вложенные значения как текст
        ])->all();

        // Для deleted_fields достаточно простой очистки, так как это плоский массив строк.
        $decodedDeletedFields = json_decode($clearedData->get('deleted_fields', '[]'), true) ?? [];
        $deletedFields = $this->sanitizer->sanitize($decodedDeletedFields, [])->all(); // Очистит каждый элемент как TEXT_FIELD по умолчанию

        $formConfig = FormConfig::fromArray($sanitizedConfigArray);
        $command = new SaveFormConfigCommand($formConfig, $deletedFields);

        try {
            $useCase->execute($command);
            return $this->success(['message' => $this->str->translate('Configuration saved successfully.')]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }
}