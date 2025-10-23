<?php

namespace UserSpace\Common\Module\Media\Src\Infrastructure;

use UserSpace\Common\Module\Media\Src\Domain\MediaApiInterface;
use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileCleanupServiceInterface;
use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileRepositoryInterface;

class TemporaryFileCleanupService implements TemporaryFileCleanupServiceInterface
{
    private const FILE_AGE_HOURS = 24;

    public function __construct(
        private readonly TemporaryFileRepositoryInterface $tempFileRepository,
        private readonly MediaApiInterface                $mediaApi
    )
    {
    }

    public function cleanup(): void
    {
        $oldFileIds = $this->tempFileRepository->findOlderThan(self::FILE_AGE_HOURS);

        if (empty($oldFileIds)) {
            return;
        }

        $deletedIds = [];
        foreach ($oldFileIds as $id) {
            // Второй параметр `true` означает полное удаление файла с сервера.
            $result = $this->mediaApi->deleteAttachment($id, true);
            if ($result !== false) {
                $deletedIds[] = $id;
            }
        }

        // Удаляем записи из временной таблицы только для тех файлов,
        // которые были успешно удалены из медиабиблиотеки.
        if (!empty($deletedIds)) {
            $this->tempFileRepository->remove($deletedIds);
        }
    }
}