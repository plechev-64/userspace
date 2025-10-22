<?php

namespace UserSpace\Common\Module\Grid\App\Controller;

use UserSpace\Common\Module\Grid\App\UseCase\FetchGridDataCommand;
use UserSpace\Common\Module\Grid\App\UseCase\FetchGridDataUseCase;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

#[Route(path: '/grid')]
class GridController extends AbstractController
{
    public function __construct(
        private readonly FetchGridDataUseCase $fetchGridDataUseCase
    )
    {
    }

    /**
     * Обрабатывает AJAX-запрос для получения данных грида пользователей.
     */
    #[Route(path: '/users', method: 'POST', permission: 'manage_options')]
    public function fetchUsers(Request $request): JsonResponse
    {
        return $this->handleGridRequest('users', $request);
    }

    /**
     * Обрабатывает AJAX-запрос для получения данных табличного грида пользователей.
     */
    #[Route(path: '/users-table', method: 'POST', permission: 'manage_options')]
    public function fetchUsersTable(Request $request): JsonResponse
    {
        return $this->handleGridRequest('users-table', $request);
    }

    /**
     * Обрабатывает AJAX-запрос для получения данных грида фоновых задач.
     */
    #[Route(path: '/queue-jobs', method: 'POST', permission: 'manage_options')]
    public function fetchQueueJobs(Request $request): JsonResponse
    {
        return $this->handleGridRequest('queue-jobs', $request);
    }

    /**
     * Общий обработчик для всех запросов к гридам.
     */
    private function handleGridRequest(string $gridType, Request $request): JsonResponse
    {
        $command = new FetchGridDataCommand($gridType, $request->getPostParams());

        try {
            $result = $this->fetchGridDataUseCase->execute($command);

            return $this->success([
                'items_html' => $result->itemsHtml,
                'pagination_html' => $result->paginationHtml,
                'total_items' => $result->totalItems,
                'total_pages' => $result->totalPages,
                'current_page' => $result->currentPage,
            ]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }
}