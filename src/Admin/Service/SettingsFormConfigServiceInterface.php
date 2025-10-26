<?php

namespace UserSpace\Admin\Service;

use UserSpace\Admin\SettingsConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\FormConfig;

/**
 * Сервис для создания конфигурации формы настроек.
 */
interface SettingsFormConfigServiceInterface
{
    /**
     * Возвращает объект FormConfig, заполненный данными из БД и начальными опциями.
     */
    public function getFormConfig(): FormConfig;

    /**
     * Собирает конфигурацию для формы настроек через фильтр.
     */
    public function getSettingsConfig(): SettingsConfig;
}