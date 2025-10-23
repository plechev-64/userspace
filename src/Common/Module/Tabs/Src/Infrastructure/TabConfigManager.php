<?php

namespace UserSpace\Common\Module\Tabs\Src\Infrastructure;

use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;

/**
 * Управляет сохранением и загрузкой конфигурации вкладок из базы данных.
 */
class TabConfigManager
{
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
        return $this->optionManager->update(self::OPTION_NAME, $config);
    }
}