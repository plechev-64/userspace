<?php

namespace UserSpace\Common\Module\Queue\App\UseCase\GetStatus;

use UserSpace\Common\Module\Grid\Src\Domain\DTO\GridRequestParamsDto;
use UserSpace\Common\Module\Grid\Src\Infrastructure\QueueJobsGrid;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueStatus;

class GetQueueStatusUseCase
{
    public function __construct(
        private readonly QueueStatus   $status,
        private readonly QueueJobsGrid $grid
    )
    {
    }

    public function execute(GetQueueStatusCommand $command): GetQueueStatusResult
    {
        // 1. Получаем данные для виджета статуса
        $statusData = $this->status->getStatus();

        // 2. Получаем данные для грида
        $paramsDto = new GridRequestParamsDto([
            'page' => $command->page,
            'orderby' => $command->orderby,
            'order' => $command->order,
            'search' => $command->search,
        ]);
        $gridData = $this->grid->fetchData($paramsDto);

        // 3. Рендерим HTML для грида
        $itemsHtml = $this->grid->renderItems($gridData['items']);
        $paginationHtml = $this->grid->renderPagination($gridData['current_page'], $gridData['total_pages']);

        // 4. Возвращаем результат
        return new GetQueueStatusResult(
            $statusData,
            $itemsHtml,
            $paginationHtml
        );
    }
}