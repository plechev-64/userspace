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
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Контроллер для управления медиафайлами.
 */
#[Route(path: '/media')]
class MediaController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface $str,
        private readonly SanitizerInterface    $sanitizer
    )
    {
    }

    #[Route(path: '/upload', method: 'POST', permission: 'upload_files')]
    public function handleUpload(Request $request, UploadFileUseCase $uploadFileUseCase): JsonResponse
    {
        // 1. Санитизируем "сырые" POST-данные
        $clearedPost = $this->sanitizer->sanitize($request->getPostParams(), [
            'config' => SanitizerRule::TEXT_FIELD,
            'signature' => SanitizerRule::TEXT_FIELD,
        ]);

        $configJson = $clearedPost->get('config', '{}');
        $signature = $clearedPost->get('signature');

        $decodedConfig = json_decode($configJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(['message' => $this->str->translate('Invalid configuration format.')], 400);
        }

        // 2. Санитизируем данные *внутри* декодированного JSON
        $clearedConfig = $this->sanitizer->sanitize($decodedConfig, [
            'name' => SanitizerRule::KEY,
            'multiple' => SanitizerRule::BOOL,
            'allowedTypes' => SanitizerRule::TEXT_FIELD,
            'maxSize' => SanitizerRule::FLOAT,
            'minWidth' => SanitizerRule::INT,
            'minHeight' => SanitizerRule::INT,
            'maxWidth' => SanitizerRule::INT,
            'maxHeight' => SanitizerRule::INT,
        ]);

        $command = new UploadFileCommand(
            $_FILES['file'] ?? [],
            $signature,
            $clearedConfig->get('name'),
            $clearedConfig->get('multiple', false),
            $clearedConfig->get('allowedTypes'),
            $clearedConfig->get('maxSize'),
            $clearedConfig->get('minWidth'),
            $clearedConfig->get('minHeight'),
            $clearedConfig->get('maxWidth'),
            $clearedConfig->get('maxHeight')
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
     * @param DeleteFileUseCase $deleteFileUseCase
     * @return JsonResponse
     */
    #[Route(path: '/(?P<id>[\d]+)', method: 'DELETE', permission: 'upload_files')]
    public function deleteAttachment(int $id, DeleteFileUseCase $deleteFileUseCase): JsonResponse
    {
        // Хотя роутер уже валидирует ID как число, дополнительная санитизация - хорошая практика.
        $sanitizedId = (int)$id;
        try {
            $deleteFileUseCase->execute($sanitizedId);

            return $this->success([
                'message' => $this->str->translate('File deleted successfully.'),
                'attachment_id' => $id,
            ]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }
}