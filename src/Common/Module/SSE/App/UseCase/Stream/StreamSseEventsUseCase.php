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
    private const INITIAL_RETRY_MS = 0; // Начальное значение для активного пользователя
    private const MAX_RETRY_MS = 10000;
    private const RETRY_INCREMENT_MS = 2000;
    private const GUEST_RETRY_MS = 10000;
    private const TRANSIENT_PREFIX = 'usp_sse_retry_';

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
        if ($userId === false) {
            $this->_sendUnauthorizedAndExit();
        }

        $lastEventId = $command->lastEventId;

        $this->_sendSseHeaders();
        // Устанавливаем время жизни скрипта чуть больше, чем наш цикл
        set_time_limit(self::CONNECTION_LIFETIME + 5);

        // Делаем одну первоначальную проверку на наличие новых событий
        if ($lastEventId > 0) {
            $events = $this->repository->findNewerThan($lastEventId, $userId);
        } else {
            // Если это первое подключение (lastEventId = 0), получаем только одно, самое последнее событие.
            // Это не считается "новым" событием, поэтому не сбрасываем retry.
            $latestEvent = $this->repository->findLatest($userId);
            $events = $latestEvent ? [$latestEvent] : [];
        }

        if ($userId) {
            $this->_handleAuthenticatedUser($events, $userId, $lastEventId);
        } else {
            $this->_handleGuestUser($command, $events);
        }

        $this->_flushAndExit();
    }

    private function sendEvents(array $events, int &$lastEventId): void
    {
        foreach ($events as $event) {
            echo "id: " . $this->str->escHtml($event->id) . "\n";
            echo "event: " . $this->str->escHtml($event->event_type) . "\n";
            echo "data: " . $event->payload . "\n\n";
            $lastEventId = $event->id;
        }
    }

    /**
     * Обрабатывает логику для авторизованного пользователя.
     */
    private function _handleAuthenticatedUser(array $events, int $userId, int $lastEventId): void
    {
        $transientKey = self::TRANSIENT_PREFIX . $userId;

        if (!empty($events)) {
            $this->_streamActiveUserEvents($userId, $lastEventId, $transientKey, $events);
        } else {
            $this->_setInactiveUserRetry($userId, $lastEventId, $transientKey);
        }
    }

    /**
     * Обрабатывает логику для гостевого пользователя.
     */
    private function _handleGuestUser(StreamSseEventsCommand $command, array $events): void
    {
        if (!empty($events)) {
            $lastEventId = $command->lastEventId;
            $this->sendEvents($events, $lastEventId);
        }
        // Гость всегда получает максимальную задержку, чтобы не занимать ресурсы сервера.
        echo "retry: " . self::GUEST_RETRY_MS . "\n\n";
    }

    /**
     * Запускает цикл "длинного опроса" для активного пользователя.
     */
    private function _streamActiveUserEvents(int $userId, int $lastEventId, string $transientKey, array $events): void
    {
        $transient = $this->optionManager->transient();
        $retry = self::MAX_RETRY_MS;

        if (!$lastEventId && !empty($events)) {
            $this->sendEvents($events, $lastEventId);
        }

        if ($this->stream($lastEventId, $userId)) {
            $retry = self::INITIAL_RETRY_MS;
            $transient->set($transientKey, self::INITIAL_RETRY_MS, 60);
        }

        // После завершения цикла просим клиента переподключиться немедленно или через MAX_RETRY_MS, если это первое подключение.
        echo "retry: $retry\n\n";
    }

    /**
     * Устанавливает и отправляет задержку `retry` для неактивного пользователя.
     */
    private function _setInactiveUserRetry(int $userId, int $lastEventId, string $transientKey): void
    {
        $transient = $this->optionManager->transient();
        $currentRetry = $transient->get($transientKey);

        if ($currentRetry !== false) {
            // Если транзиент существует (пользователь был недавно активен), увеличиваем задержку.
            $newRetry = (int)$currentRetry + self::RETRY_INCREMENT_MS;

            if ($newRetry >= self::MAX_RETRY_MS) {
                // Достигли максимума, удаляем транзиент, чтобы пользователь стал "холодным".
                $transient->delete($transientKey);
                $newRetry = self::MAX_RETRY_MS;
            }

            if ($newRetry <= (self::MAX_RETRY_MS / 2) && $this->stream($lastEventId, $userId)) {
                $newRetry = self::INITIAL_RETRY_MS;
            }

            // Увеличиваем задержку и обновляем транзиент.
            $transient->set($transientKey, $newRetry, 60);
        } else {
            // Если транзиента нет, это "холодный" пользователь. Устанавливаем максимальную задержку.
            $newRetry = self::MAX_RETRY_MS;
        }
        echo "retry: " . $newRetry . "\n\n";
    }

    /**
     * @param $lastEventId
     * @param $userId
     * @return int
     */
    private function stream($lastEventId, $userId): int
    {
        $startTime = time();
        $countNewEvent = 0;
        while (time() - $startTime < self::CONNECTION_LIFETIME) {

            $events = $this->repository->findNewerThan($lastEventId, $userId);

            if (!empty($events)) {
                $countNewEvent += count($events);
                $this->sendEvents($events, $lastEventId);
            }

            @ob_flush();
            flush();

            echo ":keep-alive\n\n";

            sleep(1);
        }

        return $countNewEvent;
    }

    /**
     * Отправляет HTTP-заголовки, необходимые для SSE.
     */
    private function _sendSseHeaders(): void
    {
        // Немедленно закрываем сессию, чтобы не блокировать другие запросы от этого же пользователя.
        @session_write_close();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Отключаем буферизацию для Nginx
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

    /**
     * Отправляет HTTP-ответ с ошибкой авторизации и завершает выполнение.
     */
    private function _sendUnauthorizedAndExit(): void
    {
        header("HTTP/1.1 401 Unauthorized");
        exit('Invalid or expired token.');
    }

    /**
     * Сбрасывает буфер вывода и завершает выполнение скрипта.
     */
    private function _flushAndExit(): void
    {
        @ob_flush();
        flush();
        exit;
    }
}