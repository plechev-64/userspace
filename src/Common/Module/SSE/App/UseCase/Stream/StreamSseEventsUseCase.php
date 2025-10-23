<?php

namespace UserSpace\Common\Module\SSE\App\UseCase\Stream;

use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Core\SecurityHelperInterface;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Use Case для потоковой передачи Server-Sent Events.
 */
class StreamSseEventsUseCase
{
    /**
     * Время жизни одного SSE-соединения в секундах.
     */
    private const CONNECTION_LIFETIME = 5;

    public function __construct(
        private readonly SseEventRepositoryInterface $repository,
        private readonly StringFilterInterface       $str,
        private readonly SecurityHelperInterface     $securityHelper,
        private readonly OptionManagerInterface      $optionManager
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

        // Устанавливаем время жизни скрипта чуть больше, чем наш цикл
        set_time_limit(self::CONNECTION_LIFETIME + 5);

        $hasSentData = false; // Флаг для отслеживания отправки "полезных" данных
        $startTime = time();
        $lastEventId = $command->lastEventId;
        $cacheKey = $userId ? "usp_sse_new_event_for_{$userId}" : 'usp_sse_new_event_for_guest';
        $cache = $this->optionManager->transient();

        // Запускаем цикл "длинного опроса"
        while (time() - $startTime < self::CONNECTION_LIFETIME) {
            // Сначала делаем быструю проверку флага в кэше
            //if ($cache->get($cacheKey)) {
                // Только если флаг есть, делаем "тяжелый" запрос в БД
                $events = $this->repository->findNewerThan($lastEventId, $userId);

                if (!empty($events)) {
                    foreach ($events as $event) {
                        echo "id: " . $this->str->escHtml($event->id) . "\n";
                        echo "event: " . $this->str->escHtml($event->event_type) . "\n";
                        echo "data: " . $event->payload . "\n\n";
                        echo "startEventId: " . $this->str->escHtml($lastEventId) . "\n";
                        $lastEventId = $event->id;
                        $hasSentData = true; // Устанавливаем флаг, что данные были отправлены
                    }
                    // После отправки событий удаляем флаг из кэша
                    //$cache->delete($cacheKey);
                //}
            }

            // Отправляем комментарий, чтобы соединение не закрылось по таймауту
            echo ":keep-alive\n\n";

            // Сбрасываем буфер вывода, чтобы отправить данные клиенту
            @ob_flush();
            flush();

            // "Спим" одну секунду перед следующей проверкой
            sleep(1);
        }

        // Если за все время жизни соединения не было отправлено реальных данных,
        if (!$hasSentData) {
            echo "retry: 10000\n\n";
        }else{
            echo "retry: 0\n\n";
        }

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