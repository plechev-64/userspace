<?php

namespace UserSpace\Common\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Media\MediaApiInterface;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\SecurityHelper;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\User\UserApiInterface;

class UserController extends AbstractController
{
    public const AVATAR_META_KEY = 'usp_avatar_id';

    public function __construct(
        private readonly UserApiInterface      $userApi,
        private readonly MediaApiInterface     $mediaApi,
        private readonly StringFilterInterface $str,
        private readonly SecurityHelper        $securityHelper
    )
    {
    }

    /**
     * Обрабатывает загрузку и установку нового аватара для текущего пользователя.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route(path: '/user/avatar', method: 'POST', permission: 'read')]
    public function updateAvatar(Request $request): JsonResponse
    {
        $attachmentId = (int)$request->getPost('attachmentId');
        $userId = $this->userApi->getCurrentUserId();

        if (!$userId) {
            return $this->error(['message' => $this->str->translate('You must be logged in to update your avatar.')], 403);
        }

        if (!$attachmentId) {
            return $this->error(['message' => $this->str->translate('Attachment ID is missing.')], 400);
        }

        // Проверяем, что файл является изображением
        if (!$this->mediaApi->isAttachmentImage($attachmentId)) {
            return $this->error(['message' => $this->str->translate('The uploaded file is not an image.')], 400);
        }

        // Сохраняем ID аватара в мета-поле пользователя
        $this->userApi->updateUserMeta($userId, self::AVATAR_META_KEY, $attachmentId);

        return $this->success([
            'message' => $this->str->translate('Avatar updated successfully.'),
        ]);
    }

    #[Route(path: '/user/sse-token', method: 'POST')]
    public function generateSseToken(): JsonResponse
    {
        $userId = $this->userApi->getCurrentUserId();

        if ($userId === 0) {
            return new JsonResponse(['message' => 'Authentication required.'], 401);
        }

        $payload = [
            'user_id' => $userId,
            'exp' => time() + HOUR_IN_SECONDS, // Токен действителен 1 час
        ];

        $signature = $this->securityHelper->sign($payload);
        $token = base64_encode(wp_json_encode($payload));

        return new JsonResponse([
            'token' => $token,
            'signature' => $signature,
            'expires_in' => HOUR_IN_SECONDS,
        ]);
    }
}