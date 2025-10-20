<?php

namespace UserSpace\WpAdapter;

use UserSpace\Core\Media\MediaApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Адаптер для функций WordPress, связанных с медиафайлами.
 */
class MediaApi implements MediaApiInterface
{
    public function getAttachmentImageUrl(int $attachmentId, array|string $size = 'thumbnail'): false|string
    {
        return wp_get_attachment_image_url($attachmentId, $size);
    }

    public function getAvatarUrl(mixed $idOrEmail, array $args = []): false|string
    {
        return get_avatar_url($idOrEmail, $args);
    }

    public function handleUpload(array $file, array $overrides = []): array|false
    {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        return wp_handle_upload($file, $overrides);
    }

    public function insertAttachment(array $attachmentData, string $filename): int|\WP_Error
    {
        if (!function_exists('wp_insert_attachment')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        return wp_insert_attachment($attachmentData, $filename);
    }

    public function generateAttachmentMetadata(int $attachmentId, string $filename): array|false
    {
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        return wp_generate_attachment_metadata($attachmentId, $filename);
    }

    public function updateAttachmentMetadata(int $attachmentId, array $metadata): bool
    {
        if (!function_exists('wp_update_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        return wp_update_attachment_metadata($attachmentId, $metadata);
    }

    public function isAttachmentImage(int $attachmentId): bool
    {
        return wp_attachment_is_image($attachmentId);
    }

    public function getMimeTypeIconUrl(int|string $mime): false|string
    {
        $icon_html = wp_mime_type_icon($mime);
        if (preg_match('/src=["\']([^"\']+)["\']/', $icon_html, $matches)) {
            return $matches[1];
        }

        return false;
    }
}