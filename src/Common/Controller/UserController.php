<?php

namespace UserSpace\Common\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

class UserController extends AbstractController
{
    public const AVATAR_META_KEY = 'usp_avatar_id';

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
        $attachmentId = $request->getPost('attachmentId', 'int');
        $userId = get_current_user_id();

        if (!$userId) {
            return $this->error(['message' => __('You must be logged in to update your avatar.', 'usp')], 403);
        }

        if (!$attachmentId) {
            return $this->error(['message' => __('Attachment ID is missing.', 'usp')], 400);
        }

        // Проверяем, что файл является изображением
        if (!wp_attachment_is_image($attachmentId)) {
            return $this->error(['message' => __('The uploaded file is not an image.', 'usp')], 400);
        }

        // Сохраняем ID аватара в мета-поле пользователя
        update_user_meta($userId, self::AVATAR_META_KEY, $attachmentId);

        return $this->success([
            'message' => __('Avatar updated successfully.', 'usp'),
        ]);
    }
}