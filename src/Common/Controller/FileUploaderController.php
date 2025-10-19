<?php

namespace UserSpace\Common\Controller;

use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\AllowedTypesValidator;
use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\ImageDimensionsValidator;
use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\MaxFileSizeValidator;
use UserSpace\Common\Service\UploadedFileValidator;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\SecurityHelper;
use UserSpace\Core\StringFilterInterface;

class FileUploaderController extends AbstractController
{
    public function __construct(
        private readonly SecurityHelper        $securityHelper,
        private readonly StringFilterInterface $str
    )
    {
    }

    #[Route(path: '/files/upload', method: 'POST', permission: 'upload_files')]
    public function handleUpload(Request $request): JsonResponse
    {
        if (empty($_FILES['file'])) {
            return $this->error(['message' => $this->str->translate('No file was uploaded.')], 400);
        }

        $file = $_FILES['file'];
        $configJson = $request->getPost('config');
        $signature = $request->getPost('signature');

        // --- Серверная валидация ---
        if ($configJson && $signature) {
            $config = json_decode($this->str->unslash($configJson), true);

            if (!$config || !$this->securityHelper->validate($config, $signature)) {
                return $this->error(['message' => $this->str->translate('Invalid request signature.')], 403);
            }

            $rules = [];
            if (!empty($config['allowedTypes'])) {
                $rules[] = new AllowedTypesValidator($config['allowedTypes']);
            }
            if (!empty($config['maxSize'])) {
                $rules[] = new MaxFileSizeValidator((float)$config['maxSize']);
            }
            if (!empty($config['minWidth']) || !empty($config['minHeight']) || !empty($config['maxWidth']) || !empty($config['maxHeight'])) {
                $rules[] = new ImageDimensionsValidator($config['minWidth'] ?: null, $config['minHeight'] ?: null, $config['maxWidth'] ?: null, $config['maxHeight'] ?: null);
            }

            $fileValidator = new UploadedFileValidator();

            // Валидируем файл, используя конфигурацию из запроса
            if (!$fileValidator->validate($file, $rules)) {
                // Возвращаем первую ошибку валидации
                $errors = $fileValidator->getErrors();
                return $this->error(['message' => $errors[0]], 400);
            }
        }
        // --- Конец серверной валидации ---

        // Подключаем необходимые файлы WordPress для работы с загрузками
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['file'];
        $upload_overrides = ['test_form' => false];

        // Безопасно обрабатываем загрузку
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $filename = basename($movefile['file']);

            $attachment = [
                'post_mime_type' => $movefile['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content' => '',
                'post_status' => 'inherit',
            ];

            // Создаем запись в медиабиблиотеке
            $attachmentId = wp_insert_attachment($attachment, $movefile['file']);

            // Генерируем метаданные (включая миниатюры для изображений)
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attachmentId, $movefile['file']);
            wp_update_attachment_metadata($attachmentId, $attach_data);

            // Определяем URL для предпросмотра
            if (wp_attachment_is_image($attachmentId)) {
                $previewUrl = wp_get_attachment_image_url($attachmentId, 'thumbnail');
            } else {
                $previewUrl = wp_mime_type_icon($attachmentId);
            }

            return $this->success([
                'attachmentId' => $attachmentId,
                'previewUrl' => $previewUrl,
            ]);
        }

        return $this->error(['message' => $movefile['error']], 500);
    }
}