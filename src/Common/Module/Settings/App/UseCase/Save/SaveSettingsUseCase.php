<?php

namespace UserSpace\Common\Module\Settings\App\UseCase\Save;

use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileRepositoryInterface;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\String\StringFilterInterface;

class SaveSettingsUseCase
{
    private const OPTION_NAME = 'usp_settings';

    public function __construct(
        private readonly StringFilterInterface  $str,
        private readonly OptionManagerInterface $optionManager,
        private readonly TemporaryFileRepositoryInterface $tempFileRepository
    ) {
    }

    public function execute(SaveSettingsCommand $command): void
    {
        $sanitizedSettings = [];
        $payload = $command->settingsPayload;

        foreach ($payload as $key => $value) {
            // Санируем ключ
            $sanitizedKey = $this->str->sanitizeKey($key);

            // Санируем значение, учитывая, что оно может быть массивом (например, для групп чекбоксов)
            if (is_array($value)) {
                $sanitizedSettings[$sanitizedKey] = array_map([$this->str, 'sanitizeTextField'], $value);
            } else {
                $sanitizedSettings[$sanitizedKey] = $this->str->sanitizeTextField((string)$value);
            }
        }

        $this->optionManager->update(self::OPTION_NAME, $sanitizedSettings);
        $this->commitUsedFiles($sanitizedSettings);
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
                // Пока считаем все числовые значения потенциальными ID.
                // Для большей надежности можно было бы проверять get_post_type($value) === 'attachment'.
                $attachmentIds[] = (int)$value;
            }
        });

        if (!empty($attachmentIds)) {
            $this->tempFileRepository->remove($attachmentIds);
        }
    }
}