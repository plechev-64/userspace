<?php

namespace UserSpace\Core\Addon;

use UserSpace\Core\Container\ContainerInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Определяет контракт для дополнений к плагину UserSpace.
 */
interface AddonInterface
{
    /**
     * Инициализирует дополнение, регистрирует сервисы, хуки и т.д.
     *
     * @param ContainerInterface $container
     */
    public function setup(ContainerInterface $container): void;

    /**
     * Возвращает отображаемое имя дополнения.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает абсолютный путь к директории дополнения.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Возвращает путь к конфигурационному файлу контейнера DI для дополнения.
     *
     * @return ?string Null, если файл не существует.
     */
    public function getContainerConfigPath(): ?string;
}