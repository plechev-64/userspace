<?php

namespace UserSpace\Core\Media;

interface TemporaryFileCleanupServiceInterface
{
    /**
     * Находит и удаляет временные файлы, которые старше 24 часов.
     */
    public function cleanup(): void;
}