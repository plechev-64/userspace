<?php

namespace UserSpace\Common\Module\Media\App\UseCase\Upload;

/**
 * Команда для выполнения загрузки файла.
 */
class UploadFileCommand
{
    public function __construct(
        public readonly array   $file,
        public readonly ?string $signature,
        public readonly ?string $allowedTypes,
        public readonly ?float  $maxSize,
        public readonly ?int    $minWidth,
        public readonly ?int    $minHeight,
        public readonly ?int    $maxWidth,
        public readonly ?int    $maxHeight
    )
    {
    }
}