<?php

namespace UserSpace\Common\Module\Media\App\UseCase\Upload;

use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\AllowedTypesValidator;
use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\ImageDimensionsValidator;
use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\MaxFileSizeValidator;
use UserSpace\Common\Module\Media\Src\Domain\MediaApiInterface;
use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileRepositoryInterface;
use UserSpace\Common\Service\UploadedFileValidator;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\SecurityHelperInterface;
use UserSpace\Core\String\StringFilterInterface;

class UploadFileUseCase
{
    public function __construct(
        private readonly SecurityHelperInterface          $securityHelper,
        private readonly StringFilterInterface            $str,
        private readonly MediaApiInterface                $mediaApi,
        private readonly TemporaryFileRepositoryInterface $tempFileRepository
    )
    {
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
        if ($command->signature) {
            $uploaderConfig = UploaderConfig::fromCommand($command);
            $configArray = $uploaderConfig->toArray();

            if (empty($configArray) || !$this->securityHelper->validate($configArray, $command->signature)) {
                throw new UspException($this->str->translate('Invalid request signature.'), 403);
            }

            $this->validateFile($command->file, $configArray);
        }
        // --- Конец серверной валидации ---

        $upload_overrides = ['test_form' => false];
        $moveFile = $this->mediaApi->handleUpload($command->file, $upload_overrides);

        if (!$moveFile || isset($moveFile['error'])) {
            throw new UspException($moveFile['error'] ?? 'File upload failed.', 500);
        }

        $filename = basename($moveFile['file']);
        $attachment = [
            'post_mime_type' => $moveFile['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachmentId = $this->mediaApi->insertAttachment($attachment, $moveFile['file']);
        if (UspException::isWpError($attachmentId)) {
            throw new UspException($attachmentId->get_error_message(), 500);
        }

        $attach_data = $this->mediaApi->generateAttachmentMetadata($attachmentId, $moveFile['file']);
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
        if (isset($config['allowedTypes'])) {
            $rules[] = new AllowedTypesValidator($config['allowedTypes']);
        }
        if (isset($config['maxSize'])) {
            $rules[] = new MaxFileSizeValidator((float)$config['maxSize']);
        }
        if (isset($config['minWidth']) || isset($config['minHeight']) || isset($config['maxWidth']) || isset($config['maxHeight'])) {
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