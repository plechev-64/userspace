<?php

namespace UserSpace\Common\Module\Settings\App\Controller;

use UserSpace\Common\Module\Settings\App\UseCase\Save\SaveSettingsCommand;
use UserSpace\Common\Module\Settings\App\UseCase\Save\SaveSettingsUseCase;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/settings')]
class SettingsAdminController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface  $str,
        private readonly SaveSettingsUseCase    $saveSettingsUseCase
    )
    {
    }

    #[Route(path: '/save', method: 'POST', permission: 'manage_options')]
    public function saveSettings(Request $request): JsonResponse
    {
        $command = new SaveSettingsCommand($request->getPostParams());

        try {
            $this->saveSettingsUseCase->execute($command);
            return $this->success(['message' => $this->str->translate('Settings saved successfully.')]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }
}