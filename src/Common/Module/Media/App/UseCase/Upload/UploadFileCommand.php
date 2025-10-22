<?php

namespace UserSpace\Common\Module\Media\App\UseCase\Upload;

/**
 * Команда для выполнения загрузки файла.
 */
class UploadFileCommand
{
    public function __construct(
        public readonly array $file,
        public readonly ?string $configJson,
        public readonly ?string $signature
    ) {
    }
}