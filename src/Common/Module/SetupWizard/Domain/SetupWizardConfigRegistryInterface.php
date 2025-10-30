<?php

namespace UserSpace\Common\Module\SetupWizard\Domain;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для регистратора конфигураций мастера настройки.
 */
interface SetupWizardConfigRegistryInterface
{
    /**
     * Регистрирует конфигуратор, который добавит свои шаги и поля в мастер.
     * @param callable $configurator Функция, принимающая экземпляр SetupWizardConfig.
     */
    public function register(callable $configurator): void;

    /**
     * Собирает и возвращает итоговую конфигурацию мастера.
     */
    public function getWizardConfig(): SetupWizardConfig;
}