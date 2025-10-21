<?php

namespace UserSpace\Common\Module\SSE\App;

use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

if (!defined('ABSPATH')) {
    exit;
}

#[Route(path: '/sse')]
class SseController extends AbstractController
{
    private SseEventRepositoryInterface $repository;
    private StringFilterInterface $str;

    public function __construct(
        SseEventRepositoryInterface $repository,
        StringFilterInterface       $str
    )
    {
        $this->repository = $repository;
        $this->str = $str;
    }

    /**
     * Открывает поток Server-Sent Events для отправки обновлений клиенту.
     *
     * @param Request $request
     */
    #[Route(path: '/events', method: 'GET')]
    public function streamEvents(Request $request): void
    {
        // Немедленно закрываем сессию, чтобы не блокировать другие запросы от этого же пользователя.
        session_write_close();

        // Устанавливаем заголовки для SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Отключаем буферизацию для Nginx

        // Устанавливаем короткое время жизни скрипта, так как мы больше не используем длинный опрос
        set_time_limit(10);

        $last_event_id = $request->getHeader('Last-Event-ID', 0);

        $events = $this->repository->findNewerThan((int)$last_event_id);

        if (!empty($events)) {
            foreach ($events as $event) {
                echo "id: " . $this->str->escHtml($event->id) . "\n";
                echo "event: " . $this->str->escHtml($event->event_type) . "\n";
                echo "data: " . $event->payload . "\n\n";
                $last_event_id = $event->id;
            }
        } else {
            // Если новых событий нет, отправляем комментарий, чтобы соединение не закрылось по таймауту на стороне клиента
            echo ":keep-alive\n\n";
        }

        // Сбрасываем буфер вывода, чтобы отправить данные клиенту
        @ob_flush();
        flush();

        // Завершаем выполнение скрипта. Клиент автоматически переподключится.
        exit;
    }
}