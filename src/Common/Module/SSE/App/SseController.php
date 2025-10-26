<?php

namespace UserSpace\Common\Module\SSE\App;

use UserSpace\Common\Module\SSE\App\UseCase\Stream\StreamSseEventsCommand;
use UserSpace\Common\Module\SSE\App\UseCase\Stream\StreamSseEventsUseCase;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

if (!defined('ABSPATH')) {
    exit;
}

#[Route(path: '/sse')]
class SseController extends AbstractController
{
    public function __construct(
        private readonly StreamSseEventsUseCase $streamSseEventsUseCase,
        private readonly SanitizerInterface     $sanitizer
    )
    {
    }

    /**
     * Открывает поток Server-Sent Events для отправки обновлений клиенту.
     */
    #[Route(path: '/events/token/(?P<token>[a-zA-Z0-9]+)/signature/(?P<signature>[a-zA-Z0-9]+)', method: 'GET')]
    public function streamEvents(Request $request): void
    {
        // Санитизируем входящие параметры
        $clearedData = $this->sanitizer->sanitize([
            'token' => $request->getQuery('token'),
            'signature' => $request->getQuery('signature'),
            'lastEventId' => $request->getHeader('Last-Event-ID', 0)
        ], [
            'token' => SanitizerRule::TEXT_FIELD, // Токен может быть base64, поэтому TEXT_FIELD
            'signature' => SanitizerRule::TEXT_FIELD, // Подпись может быть base64, поэтому TEXT_FIELD
            'lastEventId' => SanitizerRule::INT,
        ]);

        $command = new StreamSseEventsCommand(
            $clearedData->get('token'),
            $clearedData->get('signature'),
            $clearedData->get('lastEventId')
        );

        // Use Case сам обработает ответ и завершит выполнение скрипта
        $this->streamSseEventsUseCase->execute($command);
    }
}