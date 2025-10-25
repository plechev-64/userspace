<?php

namespace UserSpace\Common\Module\Locations\App\Controller;

use UserSpace\Common\Module\Locations\App\UseCase\UpdateConfig\UpdateLocationConfigCommand;
use UserSpace\Common\Module\Locations\App\UseCase\UpdateConfig\UpdateLocationConfigUseCase;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/location')]
class LocationController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface $str,
        private readonly SanitizerInterface    $sanitizer,
    )
    {
    }

    /**
     * Обновляет конфигурацию вкладок.
     */
    #[Route(path: '/config/update', method: 'POST', permission: 'manage_options')]
    public function updateConfig(Request $request, UpdateLocationConfigUseCase $updateTabsConfigUseCase): JsonResponse
    {
        $clearedData = $this->sanitizer->sanitize($request->getPostParams(), [
            'config' => SanitizerRule::TEXT_FIELD
        ]);

        $command = new UpdateLocationConfigCommand($clearedData->get('config', '[]'));

        try {
            $updateTabsConfigUseCase->execute($command);

            return $this->success(['message' => $this->str->translate('Tabs configuration saved successfully.')]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }
}