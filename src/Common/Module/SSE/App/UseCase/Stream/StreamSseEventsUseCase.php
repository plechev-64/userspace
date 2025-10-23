<?php

namespace UserSpace\Common\Module\SSE\App\UseCase\Stream;

use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Core\SecurityHelperInterface;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Use Case для потоковой передачи Server-Sent Events.
 */
class StreamSseEventsUseCase
{
    public function __construct(
        private readonly SseEventRepositoryInterface $repository,
        private readonly StringFilterInterface       $str,
        private readonly SecurityHelperInterface              $securityHelper
    )
    {
    }

    /**
     * Выполняет всю логику потоковой передачи событий и завершает выполнение скрипта.
     *
     * @param StreamSseEventsCommand $command
     */
    public function execute(StreamSseEventsCommand $command): void
    {
        $userId = $this->getUserIdFromToken($command->token, $command->signature);

        // Если getUserIdFromToken вернул false, это значит, что токен был, но он невалидный.
        if ($userId === false) {
            header("HTTP/1.1 401 Unauthorized");
            exit('Invalid or expired token.');
        }

        // Немедленно закрываем сессию, чтобы не блокировать другие запросы от этого же пользователя.
        @session_write_close();

        // Устанавливаем заголовки для SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Отключаем буферизацию для Nginx

        // Устанавливаем короткое время жизни скрипта, так как мы больше не используем длинный опрос
        set_time_limit(10);

        $events = $this->repository->findNewerThan($command->lastEventId, $userId);

        if (!empty($events)) {
            foreach ($events as $event) {
                echo "id: " . $this->str->escHtml($event->id) . "\n";
                echo "event: " . $this->str->escHtml($event->event_type) . "\n";
                echo "data: " . $event->payload . "\n\n";
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

    /**
     * Определяет ID пользователя на основе токена.
     *
     * @param string|null $token
     * @param string|null $signature
     * @return int|false|null ID пользователя, false при невалидном токене, null при отсутствии токена.
     */
    private function getUserIdFromToken(?string $token, ?string $signature): int|false|null
    {
        // Если токена нет, это анонимный пользователь
        if (empty($token) || empty($signature)) {
            return null; // или 0, в зависимости от того, как репозиторий обрабатывает анонимов
        }

        $payload = json_decode(base64_decode($token), true);

        if (!is_array($payload) || !isset($payload['user_id']) || !isset($payload['exp'])) {
            return false; // Невалидный формат токена
        }

        // Проверяем подпись и срок действия
        if ($this->securityHelper->validate($payload, $signature) && time() < $payload['exp']) {
            return (int)$payload['user_id'];
        }

        // Токен есть, но он невалидный
        return false;
    }
}