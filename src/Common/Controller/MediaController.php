<?php

namespace UserSpace\Common\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Контроллер для управления медиафайлами.
 */
class MediaController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface $str
    )
    {
    }

    /**
     * Удаляет вложение (медиафайл) из WordPress.
     *
     * @param int $id ID вложения для удаления.
     * @return JsonResponse
     */
    #[Route(path: '/media/(?P<id>[\d]+)', method: 'DELETE', permission: 'upload_files')]
    public function deleteAttachment(int $id): JsonResponse
    {
        // Проверяем, существует ли такое вложение
        if (!get_post($id)) {
            return $this->error($this->str->translate('File not found.'), 404);
        }

        // Удаляем вложение. Второй параметр `true` означает полное удаление без перемещения в корзину.
        $result = wp_delete_attachment($id, true);

        if ($result === false) {
            return $this->error($this->str->translate('Failed to delete the file. Please try again.'), 500);
        }

        return $this->success([
            'message' => $this->str->translate('File deleted successfully.'),
            'attachment_id' => $id,
        ]);
    }
}