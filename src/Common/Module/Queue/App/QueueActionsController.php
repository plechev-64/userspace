<?php

namespace UserSpace\Common\Module\Queue\App;

use UserSpace\Common\Module\Grid\Src\Domain\DTO\GridRequestParamsDto;
use UserSpace\Common\Module\Grid\Src\Infrastructure\QueueJobsGrid;
use UserSpace\Common\Module\Queue\App\Task\Message\PingMessage;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueDispatcher;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueManager;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueStatus;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Process\BackgroundProcessManager;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/queue')]
class QueueActionsController extends AbstractController
{
    public function __construct(
        private readonly QueueDispatcher          $dispatcher,
        private readonly QueueStatus              $status,
        private readonly QueueJobsGrid            $grid,
        private readonly QueueManager             $queueManager,
        private readonly BackgroundProcessManager $backgroundProcess,
        private readonly StringFilterInterface    $str
    )
    {
    }

    /**
     * Отправляет тестовую Ping-задачу в очередь.
     */
    #[Route(path: '/ping', method: 'POST', permission: 'manage_options')]
    public function sendPing(Request $request): JsonResponse
    {
        $message = new PingMessage(time());
        $this->dispatcher->dispatch($message);

        return $this->success(['message' => $this->str->translate('Ping task has been dispatched.', 'usp')]);
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
        // Делегируем запуск фонового процесса специализированному сервису.
        $this->backgroundProcess->dispatch('/queue/run-worker');

        return $this->success(['message' => $this->str->translate('Queue processing has been triggered in the background.', 'usp')]);
    }

    /**
     * Внутренний эндпоинт, который выполняет реальную работу.
     * Вызывается фоновым curl-запросом из processNow().
     */
    #[Route(path: '/run-worker', method: 'POST')]
    public function runWorker(Request $request): JsonResponse
    {
        // Проверяем наш внутренний токен. Это гарантирует, что эндпоинт вызван только нашим плагином.
        $token = $request->getHeader('X-Worker-Token');
        if (!$token || !hash_equals(USERSPACE_WORKER_TOKEN, $token)) {
            return $this->error('Invalid worker token.', 403);
        }

        $this->queueManager->processQueueBatch();
        return $this->success(['message' => 'Worker finished.']);
    }
}