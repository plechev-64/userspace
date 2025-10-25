<?php

namespace UserSpace\Common\Module\Locations\App\UseCase\UpdateConfig;

use UserSpace\Common\Module\Locations\Src\Infrastructure\LocationConfigManager;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;
use UserSpace\Core\String\StringFilterInterface;

class UpdateLocationConfigUseCase
{
    public function __construct(
        private readonly LocationConfigManager $tabConfigManager,
        private readonly StringFilterInterface $str,
        private readonly SanitizerInterface    $sanitizer
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(UpdateLocationConfigCommand $command): void
    {
        $decodedConfig = json_decode($command->configJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UspException($this->str->translate('Invalid JSON format.'), 400);
        }

        if (!is_array($decodedConfig)) {
            throw new UspException($this->str->translate('Invalid tab configuration format.'), 400);
        }

        $sanitizedTabsConfig = $this->sanitizeTabConfigArray($decodedConfig);

        $this->tabConfigManager->save($sanitizedTabsConfig);
    }

    /**
     * Рекурсивно санитизирует массив конфигурации вкладок.
     *
     * @param array $tabsConfig
     * @return array
     */
    private function sanitizeTabConfigArray(array $tabsConfig): array
    {
        $sanitizedArray = [];
        $tabRules = $this->getTabSanitizationRules();

        foreach ($tabsConfig as $tabData) {
            if (!is_array($tabData)) {
                continue; // Пропускаем некорректные записи
            }

            $sanitizedTab = $this->sanitizer->sanitize($tabData, $tabRules)->all();

            // Рекурсивно санитизируем подвкладки
            if (isset($sanitizedTab['subTabs']) && is_array($sanitizedTab['subTabs'])) {
                $sanitizedTab['subTabs'] = $this->sanitizeTabConfigArray($sanitizedTab['subTabs']);
            }
            $sanitizedArray[] = $sanitizedTab;
        }

        return $sanitizedArray;
    }

    /**
     * Возвращает правила санитизации для свойств одной вкладки.
     * @return array<string, string>
     */
    private function getTabSanitizationRules(): array
    {
        return [
            'id' => SanitizerRule::KEY,
            'title' => SanitizerRule::TEXT_FIELD,
            'location' => SanitizerRule::KEY,
            'order' => SanitizerRule::INT,
            'parentId' => SanitizerRule::KEY,
            'isPrivate' => SanitizerRule::BOOL,
            'isDefault' => SanitizerRule::BOOL,
            'capability' => SanitizerRule::KEY,
            'icon' => SanitizerRule::TEXT_FIELD,
            'class' => SanitizerRule::TEXT_FIELD
        ];
    }
}