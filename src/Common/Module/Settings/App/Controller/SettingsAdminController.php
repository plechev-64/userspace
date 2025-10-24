<?php

namespace UserSpace\Common\Module\Settings\App\Controller;

use UserSpace\Common\Module\Settings\App\UseCase\Save\SaveSettingsCommand;
use UserSpace\Common\Module\Settings\App\UseCase\Save\SaveSettingsUseCase;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/settings')]
class SettingsAdminController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface $str, // Используется для translate
        private readonly SaveSettingsUseCase   $saveSettingsUseCase,
        private readonly SanitizerInterface    $sanitizer // Добавляем зависимость от санитайзера
    )
    {
    }

    #[Route(path: '/save', method: 'POST', permission: 'manage_options')]
    public function saveSettings(Request $request): JsonResponse
    {
        // Определяем конфигурацию санитизации для входящих POST-параметров.
        // Для общих настроек, где ключи могут быть динамическими,
        // мы можем полагаться на поведение Sanitizer по умолчанию (TEXT_FIELD для неизвестных ключей)
        // или явно указать правила для известных полей.
        $sanitizationConfig = []; // Можно добавить конкретные правила, если известны типы полей

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