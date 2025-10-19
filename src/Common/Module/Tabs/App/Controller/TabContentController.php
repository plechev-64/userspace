<?php

namespace UserSpace\Common\Module\Tabs\App\Controller;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabRenderer;
use UserSpace\Core\AssetRegistry;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

#[Route(path: '/tab-content')]
class TabContentController extends AbstractController
{
    public function __construct(
        private readonly TabManager $tabManager,
        private readonly TabRenderer $tabRenderer,
        private readonly AssetRegistry $assetRegistry
    ) {
    }

    /**
     * Возвращает HTML-контент для указанной вкладки.
     *
     * @param string $tabId
     * @return JsonResponse
     * @throws \Exception
     */
    #[Route(path: '/(?P<tabId>[a-zA-Z0-9\-_]+)', method: 'GET')]
    public function getContent(string $tabId): JsonResponse
    {
        if (empty($tabId)) {
            return $this->error(__('Tab ID is missing.', 'usp'), 400);
        }

        $foundTab = $this->tabManager->getTab($tabId);

        if (!$foundTab) {
            return $this->error(__('Tab not found.', 'usp'), 404);
        }

        // Очищаем очереди скриптов и стилей перед генерацией контента
        $this->assetRegistry->clear();

        $html = $this->tabRenderer->render($foundTab);

        $response_data = [
            'html'   => $html,
            'assets' => $this->assetRegistry->getAssets(),
        ];

        return $this->success($response_data);
    }
}