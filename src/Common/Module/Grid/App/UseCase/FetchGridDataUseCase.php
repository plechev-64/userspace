<?php

namespace UserSpace\Common\Module\Grid\App\UseCase;

use UserSpace\Common\Module\Grid\Src\Domain\DTO\GridRequestParamsDto;
use UserSpace\Common\Module\Grid\Src\Infrastructure\GridProvider;
use UserSpace\Core\Exception\UspException;

class FetchGridDataUseCase
{
    public function __construct(
        private readonly GridProvider $gridProvider
    ) {
    }

    /**
     * @throws UspException
     */
    public function execute(FetchGridDataCommand $command): FetchGridDataResult
    {
        $grid = $this->gridProvider->getGrid($command->gridType);

        $paramsDto = new GridRequestParamsDto($command->requestParams);
        $data = $grid->fetchData($paramsDto);

        $itemsHtml = $grid->renderItems($data['items']);
        $paginationHtml = $grid->renderPagination($data['current_page'], $data['total_pages']);

        return new FetchGridDataResult(
            $itemsHtml,
            $paginationHtml,
            $data['total_items'],
            $data['total_pages'],
            $data['current_page']
        );
    }
}