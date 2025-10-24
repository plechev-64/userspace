<?php

namespace UserSpace\Common\Module\SSE\App;

use UserSpace\Common\Module\SSE\App\UseCase\Stream\StreamSseEventsCommand;
use UserSpace\Common\Module\SSE\App\UseCase\Stream\StreamSseEventsUseCase;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

if (!defined('ABSPATH')) {
    exit;
}

#[Route(path: '/sse')]
class SseController extends AbstractController
{
    public function __construct(
        private readonly StreamSseEventsUseCase $streamSseEventsUseCase
    )
    {
    }

    /**
     * Открывает поток Server-Sent Events для отправки обновлений клиенту.
     */
    #[Route(path: '/events/token/(?P<token>[a-zA-Z0-9]+)/signature/(?P<signature>[a-zA-Z0-9]+)', method: 'GET')]
    public function streamEvents(Request $request): void
    {
        $command = new StreamSseEventsCommand(
            $request->getQuery('token'),
            $request->getQuery('signature'),
            (int)$request->getHeader('Last-Event-ID', 0)
        );

        // Use Case сам обработает ответ и завершит выполнение скрипта
        $this->streamSseEventsUseCase->execute($command);
    }
}