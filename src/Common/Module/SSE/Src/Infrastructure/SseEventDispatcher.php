<?php

namespace UserSpace\Common\Module\SSE\Src\Infrastructure;

use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Common\Module\SSE\Src\Domain\SseEventDispatcherInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Управляет отправкой событий для трансляции через Server-Sent Events.
 */
class SseEventDispatcher implements SseEventDispatcherInterface
{
    private const TRANSIENT_PREFIX = 'usp_sse_new_event_for_';
    private const TRANSIENT_EXPIRATION_SECONDS = 20; // Должно быть больше, чем CONNECTION_LIFETIME в UseCase

    public function __construct(
        private readonly SseEventRepositoryInterface $repository,
        private readonly OptionManagerInterface      $optionManager
    )
    {
    }

    private function setEventTransient(?int $userId): void
    {
        return;
        $cacheKey = $userId ? self::TRANSIENT_PREFIX . $userId : self::TRANSIENT_PREFIX . 'guest';
        $this->optionManager->transient()->set($cacheKey, true, self::TRANSIENT_EXPIRATION_SECONDS);
    }

    /**
     * Отправляет событие в "почтовый ящик" (БД) для последующей трансляции.
     *
     * @param string $eventType Тип события (например, 'job_processed').
     * @param array $payload Данные для передачи.
     */
    public function dispatchEvent(string $eventType, array $payload): ?int
    {
        $eventId = $this->repository->create($eventType, $payload);
        $this->setEventTransient(null); // Сигнал для гостевых/общих событий
        return $eventId;
    }

    /**
     * Отправляет событие конкретному пользователю.
     *
     * @inheritDoc
     */
    public function dispatchToUser(int $userId, string $eventType, array $payload): ?int
    {
        $eventId = $this->repository->create($eventType, $payload, $userId);
        $this->setEventTransient($userId); // Сигнал для конкретного пользователя
        return $eventId;
    }
}