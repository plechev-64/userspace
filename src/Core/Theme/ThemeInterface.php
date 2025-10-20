<?php

namespace UserSpace\Core\Theme;
use UserSpace\Core\ContainerInterface;

/**
 * Определяет контракт для тем личного кабинета.
 * Каждая тема должна предоставлять класс, реализующий этот интерфейс.
 */
interface ThemeInterface
{
    /**
     * Инициализирует тему, регистрирует хуки и т.д.
     *
     * @param ContainerInterface $container
     */
    public function setup(ContainerInterface $container): void;

    /**
     * Возвращает отображаемое имя темы.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает абсолютный путь к директории темы.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Возвращает путь к конфигурационному файлу контейнера DI.
     *
     * @return ?string Null, если файл не существует.
     */
    public function getContainerConfigPath(): ?string;

    /**
     * Возвращает путь к главному файлу шаблона темы.
     *
     * @return ?string Null, если файл не существует.
     */
    public function getTemplatePath(): ?string;
}