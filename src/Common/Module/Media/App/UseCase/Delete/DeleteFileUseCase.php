<?php

namespace UserSpace\Common\Module\Media\App\UseCase\Delete;

use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileRepositoryInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Media\MediaApiInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\WpApiInterface;

class DeleteFileUseCase
{
    public function __construct(
        private readonly MediaApiInterface $mediaApi,
        private readonly TemporaryFileRepositoryInterface $tempFileRepository,
        private readonly WpApiInterface $wpApi,
        private readonly StringFilterInterface $str
    ) {
    }

    /**
     * @throws UspException
     */
    public function execute(int $attachmentId): void
    {
        // Проверяем, существует ли такое вложение
        if (!$this->wpApi->getPost($attachmentId)) {
            throw new UspException($this->str->translate('File not found.'), 404);
        }

        // Удаляем вложение. Второй параметр `true` означает полное удаление без перемещения в корзину.
        $result = $this->mediaApi->deleteAttachment($attachmentId, true);

        if ($result === false) {
            throw new UspException($this->str->translate('Failed to delete the file. Please try again.'), 500);
        }

        // Удаляем запись из таблицы временных файлов
        $this->tempFileRepository->remove([$attachmentId]);
    }
}