<?php

namespace UserSpace\Common\Module\Queue\App\Controller;

use UserSpace\Common\Module\Queue\App\UseCase\DispatchPing\DispatchPingTaskUseCase;
use UserSpace\Common\Module\Queue\App\UseCase\GetStatus\GetQueueStatusCommand;
use UserSpace\Common\Module\Queue\App\UseCase\GetStatus\GetQueueStatusUseCase;
use UserSpace\Common\Module\Queue\App\UseCase\RunWorker\RunQueueWorkerCommand;
use UserSpace\Common\Module\Queue\App\UseCase\RunWorker\RunQueueWorkerUseCase;
use UserSpace\Common\Module\Queue\App\UseCase\TriggerWorker\TriggerQueueWorkerUseCase;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/queue')]
class QueueActionsController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface $str,
        private readonly SanitizerInterface    $sanitizer
    )
    {
    }

    /**
     * Отправляет тестовую Ping-задачу в очередь.
     */
    #[Route(path: '/ping', method: 'POST')]
    public function sendPing(Request $request, DispatchPingTaskUseCase $dispatchPingTaskUseCase): JsonResponse
    {
        $dispatchPingTaskUseCase->execute();

        return $this->success(['message' => $this->str->translate('Ping task has been dispatched.', 'usp')]);
    }

    /**
     * Получает актуальный статус очереди и данные для грида.
     */
    #[Route(
        path: '/status/page/(?P<page>\d+)/orderby/(?P<orderby>[a-zA-Z_]+)/order/(?P<order>asc|desc)',
        method: 'GET',
        permission: 'manage_options'
    )]
    public function getStatus(
        Request               $request,
        GetQueueStatusUseCase $getQueueStatusUseCase,
        int                   $page = 1,
        string                $orderby = 'id',
        string                $order = 'desc'
    ): JsonResponse
    {
        // Санитизируем входящие параметры
        $clearedData = $this->sanitizer->sanitize([
            'page' => $page, // Уже int, но можно явно указать правило
            'orderby' => $orderby,
            'order' => $order,
            'search' => $request->getQuery('search', '')
        ], [
            'page' => SanitizerRule::INT,
            'orderby' => SanitizerRule::KEY, // Ключи для сортировки должны быть безопасными
            'order' => SanitizerRule::KEY,   // Направление сортировки должно быть безопасным
            'search' => SanitizerRule::TEXT_FIELD,
        ]);

        $command = new GetQueueStatusCommand(
            $clearedData->get('page'),
            $clearedData->get('orderby'),
            $clearedData->get('order'),
            $clearedData->get('search')
        );
        $result = $getQueueStatusUseCase->execute($command);

        return $this->success([
            'status_widget' => $result->statusWidget,
            'grid' => [
                'items_html' => $result->itemsHtml,
                'pagination_html' => $result->paginationHtml,
            ]
        ]);
    }

    /**
     * Запускает обработку очереди вручную.
     */
    #[Route(path: '/process-now', method: 'POST', permission: 'manage_options')]
    public function processNow(TriggerQueueWorkerUseCase $triggerQueueWorkerUseCase): JsonResponse
    {
        $triggerQueueWorkerUseCase->execute();

        return $this->success(['message' => $this->str->translate('Queue processing has been triggered in the background.', 'usp')]);
    }

    /**
     * Внутренний эндпоинт, который выполняет реальную работу.
     * Вызывается фоновым curl-запросом из processNow().
     */
    #[Route(path: '/run-worker', method: 'POST')]
    public function runWorker(Request $request, RunQueueWorkerUseCase $runQueueWorkerUseCase): JsonResponse
    {
        $command = new RunQueueWorkerCommand($request->getHeader('X-Worker-Token'));
        try {
            $runQueueWorkerUseCase->execute($command);
            return $this->success(['message' => 'Worker finished.']);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }
}