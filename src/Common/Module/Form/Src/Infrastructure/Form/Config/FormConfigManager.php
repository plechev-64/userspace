<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Form\Config;

use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Common\Module\Form\Src\Domain\Repository\FormRepositoryInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Управляет конфигурациями форм (сохранение, загрузка).
 */
class FormConfigManager implements FormConfigManagerInterface
{
    public function __construct(private readonly FormRepositoryInterface $repository)
    {
    }


    /**
     * Сохраняет конфигурацию формы в базу данных.
     *
     * @param string $type Тип формы (например, 'registration').
     * @param FormConfig $formConfig Конфигурационный DTO формы.
     *
     * @return int|false ID вставленной/обновленной записи или false в случае ошибки.
     */
    public function save(string $type, FormConfig $formConfig): int|false
    {
        return $this->repository->createOrUpdate($type, $formConfig->toArray());
    }

    /**
     * Загружает конфигурацию формы из базы данных.
     *
     * @param string $type Тип формы.
     *
     * @return FormConfig|null Конфигурационный DTO или null, если не найдено.
     */
    public function load(string $type): ?FormConfig
    {
        $form = $this->repository->findByType($type);
        $config_json = $form->config ?? null;

        if (!$config_json) {
            return null;
        }

        $configData = json_decode($config_json, true);

        return FormConfig::fromArray($configData);
    }
}