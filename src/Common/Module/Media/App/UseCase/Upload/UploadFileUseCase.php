<?php

namespace UserSpace\Common\Module\Media\App\UseCase\Upload;

use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\AllowedTypesValidator;
use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\ImageDimensionsValidator;
use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\MaxFileSizeValidator;
use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileRepositoryInterface;
use UserSpace\Common\Service\UploadedFileValidator;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Media\MediaApiInterface;
use UserSpace\Core\SecurityHelper;
use UserSpace\Core\String\StringFilterInterface;

class UploadFileUseCase
{
    public function __construct(
        private readonly SecurityHelper $securityHelper,
        private readonly StringFilterInterface $str,
        private readonly MediaApiInterface $mediaApi,
        private readonly TemporaryFileRepositoryInterface $tempFileRepository
    ) {
    }

    /**
     * @throws UspException
     */
    public function execute(UploadFileCommand $command): UploadFileResult
    {
        if (empty($command->file)) {
            throw new UspException($this->str->translate('No file was uploaded.'), 400);
        }

        // --- Серверная валидация ---
        if ($command->configJson && $command->signature) {
            $config = json_decode($this->str->unslash($command->configJson), true);

            if (!$config || !$this->securityHelper->validate($config, $command->signature)) {
                throw new UspException($this->str->translate('Invalid request signature.'), 403);
            }

            $this->validateFile($command->file, $config);
        }
        // --- Конец серверной валидации ---

        $upload_overrides = ['test_form' => false];
        $movefile = $this->mediaApi->handleUpload($command->file, $upload_overrides);

        if (!$movefile || isset($movefile['error'])) {
            throw new UspException($movefile['error'] ?? 'File upload failed.', 500);
        }

        $filename = basename($movefile['file']);
        $attachment = [
            'post_mime_type' => $movefile['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachmentId = $this->mediaApi->insertAttachment($attachment, $movefile['file']);
        if (is_wp_error($attachmentId)) {
            throw new UspException($attachmentId->get_error_message(), 500);
        }

        $attach_data = $this->mediaApi->generateAttachmentMetadata($attachmentId, $movefile['file']);
        $this->mediaApi->updateAttachmentMetadata($attachmentId, $attach_data);

        // Добавляем ID файла в таблицу временных файлов
        $this->tempFileRepository->add($attachmentId);

        // Определяем URL для предпросмотра
        $previewUrl = $this->mediaApi->isAttachmentImage($attachmentId)
            ? $this->mediaApi->getAttachmentImageUrl($attachmentId, 'thumbnail')
            : $this->mediaApi->getMimeTypeIconUrl($attachmentId);

        return new UploadFileResult($attachmentId, $previewUrl);
    }

    /**
     * @throws UspException
     */
    private function validateFile(array $file, array $config): void
    {
        $rules = [];
        if (!empty($config['allowedTypes'])) {
            $rules[] = new AllowedTypesValidator($config['allowedTypes']);
        }
        if (!empty($config['maxSize'])) {
            $rules[] = new MaxFileSizeValidator((float)$config['maxSize']);
        }
        if (!empty($config['minWidth']) || !empty($config['minHeight']) || !empty($config['maxWidth']) || !empty($config['maxHeight'])) {
            $rules[] = new ImageDimensionsValidator($config['minWidth'] ?: null, $config['minHeight'] ?: null, $config['maxWidth'] ?: null, $config['maxHeight'] ?: null);
        }

        if (empty($rules)) {
            return;
        }

        $fileValidator = new UploadedFileValidator();

        if (!$fileValidator->validate($file, $rules)) {
            $errors = $fileValidator->getErrors();
            throw new UspException($errors[0], 400);
        }
    }
}