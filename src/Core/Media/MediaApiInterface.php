<?php

namespace UserSpace\Core\Media;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для взаимодействия с функциями WordPress, связанными с медиафайлами.
 */
interface MediaApiInterface
{
    /**
     * Получает URL изображения для вложения.
     * Обертка для wp_get_attachment_image_url().
     *
     * @param int $attachmentId ID вложения.
     * @param string|array $size Размер изображения.
     * @return string|false URL изображения или false, если не найдено.
     */
    public function getAttachmentImageUrl(int $attachmentId, string|array $size = 'thumbnail'): string|false;

    /**
     * Получает URL аватара пользователя.
     * Обертка для get_avatar_url().
     *
     * @param mixed $idOrEmail Идентификатор пользователя.
     * @param array $args Аргументы.
     * @return string|false URL аватара или false.
     */
    public function getAvatarUrl(mixed $idOrEmail, array $args = []): string|false;

    /**
     * Обрабатывает загрузку файла.
     * Обертка для wp_handle_upload().
     *
     * @param array $file Массив из $_FILES.
     * @param array $overrides Массив для переопределения поведения.
     * @return array|false
     */
    public function handleUpload(array $file, array $overrides = []): array|false;

    /**
     * Вставляет вложение в медиабиблиотеку.
     * Обертка для wp_insert_attachment().
     *
     * @param array $attachmentData Данные вложения.
     * @param string $filename Путь к файлу.
     * @return int|\WP_Error ID вложения или объект ошибки.
     */
    public function insertAttachment(array $attachmentData, string $filename): int|\WP_Error;

    /**
     * Генерирует метаданные для вложения (включая миниатюры).
     * Обертка для wp_generate_attachment_metadata().
     *
     * @param int $attachmentId ID вложения.
     * @param string $filename Путь к файлу.
     * @return array|false
     */
    public function generateAttachmentMetadata(int $attachmentId, string $filename): array|false;

    /**
     * Обновляет метаданные вложения.
     * Обертка для wp_update_attachment_metadata().
     *
     * @param int $attachmentId ID вложения.
     * @param array $metadata Новые метаданные.
     * @return bool
     */
    public function updateAttachmentMetadata(int $attachmentId, array $metadata): bool;

    /**
     * Проверяет, является ли вложение изображением.
     * Обертка для wp_attachment_is_image().
     *
     * @param int $attachmentId ID вложения.
     * @return bool
     */
    public function isAttachmentImage(int $attachmentId): bool;

    /**
     * Получает URL иконки для MIME-типа.
     * Обертка для wp_mime_type_icon().
     *
     * @param int|string $mime Mime-тип или ID вложения.
     * @return string|false URL иконки или false.
     */
    public function getMimeTypeIconUrl(int|string $mime): string|false;
}