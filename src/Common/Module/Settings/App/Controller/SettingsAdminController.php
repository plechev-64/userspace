<?php

namespace UserSpace\Common\Module\Settings\App\Controller;

use UserSpace\Admin\Service\SettingsFormConfigServiceInterface;
use UserSpace\Common\Module\Settings\App\UseCase\Save\SaveSettingsCommand;
use UserSpace\Common\Module\Settings\App\UseCase\Save\SaveSettingsUseCase;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/settings')]
class SettingsAdminController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface              $str, // Используется для translate
        private readonly SaveSettingsUseCase                $saveSettingsUseCase, // Используется для сохранения
        private readonly SanitizerInterface                 $sanitizer,
        private readonly SettingsFormConfigServiceInterface $settingsFormConfigService // Используется для получения конфигурации
    )
    {
    }

    #[Route(path: '/save', method: 'POST', permission: 'manage_options')]
    public function saveSettings(Request $request): JsonResponse
    {
        // 1. Получаем конфигурацию формы, чтобы знать типы полей.
        $formConfig = $this->settingsFormConfigService->getFormConfig();
        $sanitizationConfig = [];

        // 2. Строим конфигурацию санитизации на основе типов полей.
        foreach ($formConfig->getFields() as $field) {
            $sanitizationConfig[$field['name']] = match ($field['type']) {
                'boolean' => SanitizerRule::BOOL,
                'uploader', 'number' => SanitizerRule::INT, // ID вложений - это числа
                'url' => SanitizerRule::URL,
                'email' => SanitizerRule::EMAIL,
                'select', 'radio', 'checkbox' => SanitizerRule::KEY, // Ожидаем безопасные ключи
                'textarea' => SanitizerRule::KSES_POST, // Разрешаем безопасный HTML
                default => SanitizerRule::TEXT_FIELD, // По умолчанию очищаем как текст
            };
        }

        $clearedData = $this->sanitizer->sanitize($request->getPostParams(), $sanitizationConfig);

        $command = new SaveSettingsCommand($clearedData->all()); // Передаем полностью очищенный массив

        try {
            $this->saveSettingsUseCase->execute($command);
            return $this->success(['message' => $this->str->translate('Settings saved successfully.')]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }
}