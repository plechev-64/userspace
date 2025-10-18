<?php

namespace UserSpace\Core\Tabs;

/**
 * Управляет сохранением и загрузкой конфигурации вкладок из базы данных.
 */
class TabConfigManager
{
    private const OPTION_NAME = 'usp_tabs_config';

    /**
     * Загружает конфигурацию вкладок.
     *
     * @return array|null Возвращает массив конфигурации или null, если она не найдена.
     */
    public function load(): ?array
    {
        $config = get_option(self::OPTION_NAME);
        return is_array($config) ? $config : null;
    }

    /**
     * Сохраняет конфигурацию вкладок.
     */
    public function save(array $config): bool
    {
        return update_option(self::OPTION_NAME, $config);
    }
}