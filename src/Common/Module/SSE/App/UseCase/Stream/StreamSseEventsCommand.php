<?php

namespace UserSpace\Common\Module\SSE\App\UseCase\Stream;

/**
 * Команда для запуска потока Server-Sent Events.
 */
class StreamSseEventsCommand
{
    public function __construct(
        public readonly ?string $token,
        public readonly ?string $signature,
        public readonly int $lastEventId
    ) {
    }
}