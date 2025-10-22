<?php

namespace UserSpace\Common\Module\Media\App\Controller;

use UserSpace\Common\Module\Media\App\UseCase\Delete\DeleteFileUseCase;
use UserSpace\Common\Module\Media\App\UseCase\Upload\UploadFileCommand;
use UserSpace\Common\Module\Media\App\UseCase\Upload\UploadFileUseCase;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Контроллер для управления медиафайлами.
 */
#[Route(path: '/media')]
class MediaController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface $str
    ) {
    }

    #[Route(path: '/upload', method: 'POST', permission: 'upload_files')]
    public function handleUpload(Request $request, UploadFileUseCase $uploadFileUseCase): JsonResponse
    {
        $command = new UploadFileCommand(
            $_FILES['file'] ?? [],
            $request->getPost('config'),
            $request->getPost('signature')
        );

        try {
            $result = $uploadFileUseCase->execute($command);
            return $this->success([
                'attachmentId' => $result->attachmentId,
                'previewUrl' => $result->previewUrl,
            ]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Удаляет вложение (медиафайл) из WordPress.
     *
     * @param int $id ID вложения для удаления.
     * @return JsonResponse
     */
    #[Route(path: '/(?P<id>[\d]+)', method: 'DELETE', permission: 'upload_files')]
    public function deleteAttachment(int $id, DeleteFileUseCase $deleteFileUseCase): JsonResponse
    {
        try {
            $deleteFileUseCase->execute($id);

            return $this->success([
                'message' => $this->str->translate('File deleted successfully.'),
                'attachment_id' => $id,
            ]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }
}