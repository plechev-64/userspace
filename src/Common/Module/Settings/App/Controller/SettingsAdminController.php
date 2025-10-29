<?php

namespace UserSpace\Common\Module\Settings\App\Controller;

use UserSpace\Admin\Service\SettingsFormConfigServiceInterface;
use UserSpace\Common\Module\Form\Src\Domain\Service\FormSanitizerInterface;
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
    public function saveSettings(
        Request                   $request,
        FormSanitizerInterface    $formSanitizer
    ): JsonResponse
    {
        // 1. Получаем конфигурацию формы, чтобы знать типы полей.
        $formConfig = $this->settingsFormConfigService->getFormConfig();

        // 2. Строим конфигурацию санитизации на основе типов полей.
        $clearedData = $formSanitizer->sanitize($formConfig, $request->getPostParams());

        $command = new SaveSettingsCommand($clearedData->all()); // Передаем полностью очищенный массив

        try {
            $this->saveSettingsUseCase->execute($command);
            return $this->success(['message' => $this->str->translate('Settings saved successfully.')]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }
}