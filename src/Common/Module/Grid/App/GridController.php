<?php

namespace UserSpace\Common\Module\Grid\App;

use UserSpace\Common\Module\Grid\Src\Domain\DTO\GridRequestParamsDto;
use UserSpace\Common\Module\Grid\Src\Infrastructure\QueueJobsGrid;
use UserSpace\Common\Module\Grid\Src\Infrastructure\UserListGrid;
use UserSpace\Common\Module\Grid\Src\Infrastructure\UserListTableGrid;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

#[Route(path: '/grid')]
class GridController extends AbstractController
{
    public function __construct(
        private readonly UserListGrid      $userListGrid,
        private readonly UserListTableGrid $userListTableGrid,
        private readonly QueueJobsGrid     $queueJobsGrid
    )
    {
    }

    /**
     * Обрабатывает AJAX-запрос для получения данных грида пользователей.
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(path: '/users', method: 'POST', permission: 'manage_options')]
    public function fetchUsers(Request $request): JsonResponse
    {
        $paramsDto = new GridRequestParamsDto($request->getPostParams());
        $data = $this->userListGrid->fetchData($paramsDto);

        $itemsHtml = $this->userListGrid->renderItems($data['items']);
        $paginationHtml = $this->userListGrid->renderPagination($data['current_page'], $data['total_pages']);

        return $this->success([
            'items_html' => $itemsHtml,
            'pagination_html' => $paginationHtml,
            'total_items' => $data['total_items'],
            'total_pages' => $data['total_pages'],
            'current_page' => $data['current_page'],
        ]);
    }

    /**
     * Обрабатывает AJAX-запрос для получения данных табличного грида пользователей.
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(path: '/users-table', method: 'POST', permission: 'manage_options')]
    public function fetchUsersTable(Request $request): JsonResponse
    {
        $paramsDto = new GridRequestParamsDto($request->getPostParams());
        $data = $this->userListTableGrid->fetchData($paramsDto);

        $itemsHtml = $this->userListTableGrid->renderItems($data['items']);
        $paginationHtml = $this->userListTableGrid->renderPagination($data['current_page'], $data['total_pages']);

        return $this->success([
            'items_html' => $itemsHtml,
            'pagination_html' => $paginationHtml,
            'total_items' => $data['total_items'],
            'total_pages' => $data['total_pages'],
            'current_page' => $data['current_page'],
        ]);
    }

    /**
     * Обрабатывает AJAX-запрос для получения данных грида фоновых задач.
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(path: '/queue-jobs', method: 'POST', permission: 'manage_options')]
    public function fetchQueueJobs(Request $request): JsonResponse
    {
        $paramsDto = new GridRequestParamsDto($request->getPostParams());
        $data = $this->queueJobsGrid->fetchData($paramsDto);

        $itemsHtml = $this->queueJobsGrid->renderItems($data['items']);
        $paginationHtml = $this->queueJobsGrid->renderPagination($data['current_page'], $data['total_pages']);

        return $this->success([
            'items_html' => $itemsHtml,
            'pagination_html' => $paginationHtml,
            'total_items' => $data['total_items'],
            'total_pages' => $data['total_pages'],
            'current_page' => $data['current_page'],
        ]);
    }
}