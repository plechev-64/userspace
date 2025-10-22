<?php

namespace UserSpace\Admin\Controller;

use UserSpace\Common\Repository\TemporaryFileRepositoryInterface;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/admin')]
class SettingsAdminController extends AbstractController
{
    private const OPTION_NAME = 'usp_settings';

    public function __construct(
        private readonly StringFilterInterface  $str,
        private readonly OptionManagerInterface $optionManager,
        private readonly TemporaryFileRepositoryInterface $tempFileRepository
    )
    {
    }

    #[Route(path: '/settings', method: 'POST', permission: 'manage_options')]
    public function saveSettings(Request $request): JsonResponse
    {
        $settings = [];

        $payload = $request->getPostParams();

        foreach ($payload as $key => $value) {
            $settings[$this->str->sanitizeKey($key)] = $this->str->sanitizeTextField($value);
        }

        $this->optionManager->update(self::OPTION_NAME, $settings);
        $this->commitUsedFiles($settings);

        return $this->success(['message' => $this->str->translate('Settings saved successfully.')]);
    }

    /**
     * Находит ID файлов в сохраненных данных и удаляет их из временной таблицы.
     */
    private function commitUsedFiles(array $data): void
    {
        $attachmentIds = [];
        array_walk_recursive($data, function ($value) use (&$attachmentIds) {
            // Проверяем, является ли значение числом и похоже ли оно на ID поста
            if (is_numeric($value) && (int)$value > 0) {
                // Дополнительная проверка, что это действительно вложение, может быть избыточной,
                // но повышает надежность. Пока считаем все числовые значения потенциальными ID.
                $attachmentIds[] = (int)$value;
            }
        });

        if (!empty($attachmentIds)) {
            $this->tempFileRepository->remove($attachmentIds);
        }
    }
}