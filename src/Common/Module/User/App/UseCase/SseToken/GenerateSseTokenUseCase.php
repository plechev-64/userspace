<?php

namespace UserSpace\Common\Module\User\App\UseCase\SseToken;

use UserSpace\Core\Exception\UspException;
use UserSpace\Core\SecurityHelper;
use UserSpace\Core\String\StringFilterInterface;

class GenerateSseTokenUseCase
{
    public function __construct(
        private readonly SecurityHelper        $securityHelper,
        private readonly StringFilterInterface $str
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(int $userId): GenerateSseTokenResult
    {
        if ($userId === 0) {
            throw new UspException($this->str->translate('Authentication required.'), 401);
        }

        $expiresIn = HOUR_IN_SECONDS;
        $payload = [
            'user_id' => $userId,
            'exp' => time() + $expiresIn, // Токен действителен 1 час
        ];

        $signature = $this->securityHelper->sign($payload);
        $token = base64_encode($this->str->jsonEncode($payload));

        return new GenerateSseTokenResult($token, $signature, $expiresIn);
    }
}