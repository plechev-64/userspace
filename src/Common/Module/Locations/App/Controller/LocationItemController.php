<?php

namespace UserSpace\Common\Module\Locations\App\Controller;

use UserSpace\Common\Module\Locations\App\UseCase\GetContent\GetTabContentCommand;
use UserSpace\Common\Module\Locations\App\UseCase\GetContent\GetTabContentUseCase;
use UserSpace\Common\Module\Locations\App\UseCase\GetSettingsForm\GetItemSettingsFormCommand;
use UserSpace\Common\Module\Locations\App\UseCase\GetSettingsForm\GetItemSettingsFormUseCase;
use UserSpace\Common\Module\Locations\Src\Domain\AbstractButton;
use UserSpace\Common\Module\Locations\Src\Domain\ItemManagerInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\TemplateManagerInterface;

#[Route(path: '/location/item')]
class LocationItemController extends AbstractController
{
    public function __construct(
        private readonly SanitizerInterface       $sanitizer,
        private readonly ItemManagerInterface     $tabManager,
        private readonly TemplateManagerInterface $templateManager
    )
    {
    }

    /**
     * Возвращает HTML-контент для указанной вкладки.
     */
    #[Route(path: '/content/(?P<tabId>[a-zA-Z0-9\-_]+)', method: 'GET')]
    public function getContent(string $tabId, GetTabContentUseCase $getTabContentUseCase): JsonResponse
    {
        $clearedData = $this->sanitizer->sanitize(['tabId' => $tabId], ['tabId' => SanitizerRule::KEY]);

        $command = new GetTabContentCommand($clearedData->get('tabId'));

        try {
            $result = $getTabContentUseCase->execute($command);
            return $this->success([
                'html' => $result->html,
                'assets' => $result->assets,
            ]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route(path: '/settings', method: 'POST', permission: 'manage_options')]
    public function getSettingsForm(Request $request, GetItemSettingsFormUseCase $getTabSettingsFormUseCase): JsonResponse
    {
        $clearedData = $this->sanitizer->sanitize($request->getPostParams(), [
            'tabConfig' => SanitizerRule::TEXT_FIELD
        ]);

        $command = new GetItemSettingsFormCommand($clearedData->get('tabConfig', '{}'));

        try {
            $result = $getTabSettingsFormUseCase->execute($command); // Получаем результат с объектом формы

            // Рендерим шаблон, передавая в него объект формы
            $html = $this->templateManager->render('admin/location/item/form-settings', [
                'form' => $result->form,
            ]);
            return $this->success(['html' => $html]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route(path: '/action/(?P<itemId>[a-zA-Z0-9\-_]+)', method: 'POST', permission: 'read')]
    public function handleAction(string $itemId, Request $request): JsonResponse
    {
        $clearedItemId = $this->sanitizer->sanitize(['itemId' => $itemId], ['itemId' => SanitizerRule::KEY])->get('itemId');

        // Находим нужный элемент (кнопку) по ID
        $item = $this->tabManager->getItem($clearedItemId);

        if (!$item || !$item instanceof AbstractButton) {
            return $this->error(['message' => 'Action item not found or is not a button.'], 404);
        }

        // Проверяем права доступа
        if (!$item->canView()) {
            return $this->error(['message' => 'You do not have permission to perform this action.'], 403);
        }

        try {
            // Выполняем логику кнопки
            $result = $item->handleAction($request->getPostParams());
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error(['message' => $e->getMessage()], 500);
        }
    }
}