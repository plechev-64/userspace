<?php

namespace UserSpace\Common\Module\Tabs\App\Controller;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabConfigManager;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

#[Route(path: '/tabs-config')]
class TabsConfigAdminController extends AbstractController
{
    public function __construct(private readonly TabConfigManager $tabConfigManager)
    {
    }

    /**
     * Обновляет конфигурацию вкладок.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(path: '/update', method: 'POST', permission: 'manage_options')]
    public function updateConfig(Request $request): JsonResponse
    {
        $config = json_decode($request->getPost('config', '[]'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(__('Invalid JSON format.', 'usp'), 400);
        }

        $isSaved = $this->tabConfigManager->save($config);

        if (!$isSaved) {
            return $this->error(__('Failed to save tabs configuration.', 'usp'), 500);
        }

        return $this->success(['message' => __('Tabs configuration saved successfully.', 'usp')]);
    }
}