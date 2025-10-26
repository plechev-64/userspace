<?php

namespace UserSpace\Common\Module\Locations\Src\Infrastructure;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;

/**
 * Управляет сохранением и загрузкой конфигурации вкладок из базы данных.
 */
class LocationConfigManager
{
    /** @todo переименовать опцию */
    private const OPTION_NAME = 'usp_tabs_config';

    public function __construct(private readonly OptionManagerInterface $optionManager)
    {
    }

    /**
     * Загружает конфигурацию вкладок.
     *
     * @return array|null Возвращает массив конфигурации или null, если она не найдена.
     */
    public function load(): ?array
    {
        $config = $this->optionManager->get(self::OPTION_NAME);
        return is_array($config) ? $config : null;
    }

    /**
     * Сохраняет конфигурацию вкладок.
     */
    public function save(array $config): bool
    {
        // Фильтруем конфигурацию, чтобы удалить все "обзорные" вкладки.
        // Они создаются динамически и не должны сохраняться в БД.
        $filteredConfig = array_filter($config, static function ($item) {
            return !isset($item['id']) || !str_contains($item['id'], AbstractTab::OVERVIEW_POSTFIX);
        });

        // Переиндексируем массив, чтобы избежать проблем с JSON-кодированием,
        // если после фильтрации останутся не-последовательные ключи.
        $finalConfig = array_values($filteredConfig);

        return $this->optionManager->update(self::OPTION_NAME, $finalConfig);
    }
}