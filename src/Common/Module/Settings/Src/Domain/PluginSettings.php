<?php

namespace UserSpace\Common\Module\Settings\Src\Domain;

use UserSpace\Common\Module\Settings\App\SettingsEnum;

class PluginSettings implements PluginSettingsInterface
{
    public const OPTION_NAME = 'usp_settings';

    /**
     * @var array|null Кэш для хранения настроек. null означает, что настройки еще не загружены.
     */
    private ?array $settings = null;

    public function __construct(
        private readonly OptionManagerInterface $optionManager
    )
    {
    }

    public function get(SettingsEnum|string $key, mixed $default = null): mixed
    {
        $this->_loadSettings();
        $key = is_string($key) ? $key : $key->value;
        return $this->settings[$key] ?? $default;
    }

    public function all(): array
    {
        $this->_loadSettings();
        return $this->settings;
    }

    public function update(SettingsEnum|string $key, mixed $value): void
    {
        $this->_loadSettings();
        $key = is_string($key) ? $key : $key->value;
        $this->settings[$key] = $value;

        // Сохраняем обновленный массив настроек в базу данных
        $this->optionManager->update(self::OPTION_NAME, $this->settings);
    }

    public function updateAll(array $value): void
    {
        $this->_loadSettings();
        $this->settings = $value;

        // Сохраняем обновленный массив настроек в базу данных
        $this->optionManager->update(self::OPTION_NAME, $value);
    }

    /**
     * Загружает настройки из базы данных, если они еще не были загружены.
     */
    private function _loadSettings(): void
    {
        // Загружаем только один раз за запрос
        if ($this->settings !== null) {
            return;
        }

        $this->settings = $this->optionManager->get(self::OPTION_NAME, []);
    }
}