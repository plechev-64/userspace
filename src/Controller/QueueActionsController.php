<?php

namespace UserSpace\Controller;

use UserSpace\Core\Grid\DTO\GridRequestParamsDto;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Queue\QueueDispatcher;
use UserSpace\Core\Queue\QueueManager;
use UserSpace\Core\Queue\QueueStatus;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Grid\QueueJobsGrid;
use UserSpace\JobHandler\Message\PingMessage;

#[Route(path: '/queue')]
class QueueActionsController extends AbstractController
{
    public function __construct(
        private readonly QueueDispatcher $dispatcher,
        private readonly QueueStatus $status,
        private readonly QueueJobsGrid $grid,
        private readonly QueueManager $queueManager
    ) {
    }

    /**
     * Отправляет тестовую Ping-задачу в очередь.
     */
    #[Route(path: '/ping', method: 'POST', permission: 'manage_options')]
    public function sendPing(Request $request): JsonResponse
    {
        $message = new PingMessage(time());
        $this->dispatcher->dispatch($message);

        return $this->success(['message' => __('Ping task has been dispatched.', 'usp')]);
    }

    /**
     * Получает актуальный статус очереди и данные для грида.
     */
    #[Route(path: '/status/page/(?P<page>\d+)/orderby/(?P<orderby>[a-zA-Z_]+)/order/(?P<order>asc|desc)', method: 'GET', permission: 'manage_options')]
    public function getStatus(Request $request, int $page = 1, string $orderby = 'id', string $order = 'desc'): JsonResponse
    {
        // 1. Получаем данные для виджета статуса
        $statusData = $this->status->getStatus();

        // 2. Получаем данные для грида (используем параметры из запроса или значения по умолчанию)
        $paramsDto = new GridRequestParamsDto([
            'page' => $page,
            'orderby' => $orderby,
            'order' => $order,
            'search' => $request->getQuery('search', ''), // Поиск оставим в query string для простоты
        ]);
        $gridData = $this->grid->fetchData($paramsDto);

        // 3. Рендерим HTML для грида
        $itemsHtml = $this->grid->renderItems($gridData['items']);
        $paginationHtml = $this->grid->renderPagination($gridData['current_page'], $gridData['total_pages']);

        // 4. Собираем все в один ответ
        return $this->success([
            'status_widget' => $statusData,
            'grid' => [
                'items_html' => $itemsHtml,
                'pagination_html' => $paginationHtml,
            ]
        ]);
    }

    /**
     * Запускает обработку очереди вручную.
     */
    #[Route(path: '/process-now', method: 'POST', permission: 'manage_options')]
    public function processNow(Request $request): JsonResponse
    {
        $this->queueManager->processQueueBatch();

        return $this->success(['message' => __('Queue processing has been triggered.', 'usp')]);
    }
}