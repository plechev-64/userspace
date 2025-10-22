<?php

namespace UserSpace\Common\Module\User\App\UseCase\SseToken;

/**
 * Результат успешной генерации SSE-токена.
 */
class GenerateSseTokenResult
{
    public function __construct(
        public readonly string $token,
        public readonly string $signature,
        public readonly int    $expiresIn
    ) {
    }
}