<?php

namespace UserSpace\Common\Module\Media\Src\Domain;

interface TemporaryFileCleanupServiceInterface
{
    /**
     * Находит и удаляет временные файлы, которые старше 24 часов.
     */
    public function cleanup(): void;
}