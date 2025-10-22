<?php

namespace UserSpace\Common\Service;

interface TemporaryFileCleanupServiceInterface
{
    /**
     * Находит и удаляет временные файлы, которые старше 24 часов.
     */
    public function cleanup(): void;
}