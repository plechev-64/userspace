<?php

namespace UserSpace\Core\Theme;

use UserSpace\Common\Addon\AddonInterface;

/**
 * Определяет контракт для тем личного кабинета.
 * Каждая тема должна предоставлять класс, реализующий этот интерфейс.
 */
interface ThemeInterface extends AddonInterface
{
    /**
     * Возвращает путь к главному файлу шаблона темы.
     *
     * @return ?string Null, если файл не существует.
     */
    public function getTemplatePath(): ?string;

    public function getSlug(): string;

}