<?php

namespace UserSpace\Common\Module\Settings\App\UseCase\Save;

use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileRepositoryInterface;
use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Common\Module\Settings\Src\Domain\PluginSettings;

class SaveSettingsUseCase
{
    public function __construct(
        private readonly OptionManagerInterface           $optionManager,
        private readonly TemporaryFileRepositoryInterface $tempFileRepository
    )
    {
    }

    public function execute(SaveSettingsCommand $command): void
    {
        // Данные уже санитизированы контроллером.
        $this->optionManager->update(PluginSettings::OPTION_NAME, $command->settingsPayload);
        $this->commitUsedFiles($command->settingsPayload);
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