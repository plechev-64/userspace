<?php

namespace UserSpace\Common\Module\Media\App\UseCase\Upload;

/**
 * Результат успешной загрузки файла.
 */
class UploadFileResult
{
    public function __construct(
        public readonly int $attachmentId,
        public readonly string $previewUrl
    ) {
    }
}