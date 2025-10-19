<?php

namespace UserSpace\Common\Module\SSE\App;

use UserSpace\Core\Helper\StringFilterInterface;
use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

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
        global $wpdb;

        // Немедленно закрываем сессию, чтобы не блокировать другие запросы от этого же пользователя.
        session_write_close();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Отключаем буферизацию для Nginx

        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);

        $last_event_id = $request->getHeader('Last-Event-ID', 0);
        $table_name = $wpdb->prefix . 'userspace_sse_events';

        $time_limit = 50;
        set_time_limit($time_limit + 5);
        $start_time = time();

        while (time() - $start_time < $time_limit) {
            $events = $this->repository->findNewerThan((int)$last_event_id);

            if ($events) {
                foreach ($events as $event) {
                    echo "id: " . $this->str->escHtml($event->id) . "\n";
                    echo "event: " . $this->str->escHtml($event->event_type) . "\n";
                    echo "data: " . $event->payload . "\n\n";
                    $last_event_id = $event->id;
                }
            } else {
                echo ":keep-alive\n\n";
            }

            // Удаляем отправленные события, чтобы не накапливать их в БД
            // В высоконагруженной системе здесь может быть гонка состояний, но для большинства случаев это приемлемо.
            $this->repository->deleteOlderThanOrEqual((int)$last_event_id);

            @ob_flush();
            flush();

            if (connection_aborted()) break;
            sleep(2);
        }
        exit;
    }
}