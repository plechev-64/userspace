<?php

namespace UserSpace\Form;

use UserSpace\Core\Database\QueryBuilder;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Управляет конфигурациями форм (сохранение, загрузка).
 */
class FormManager
{

    private readonly \wpdb $wpdb;
    private readonly string $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'userspace_forms';
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
        $data = [
            'type' => $type,
            'config' => wp_json_encode($formConfig->toArray(), JSON_UNESCAPED_UNICODE),
        ];

        $existing = $this->wpdb->get_var($this->wpdb->prepare("SELECT id FROM {$this->table_name} WHERE type = %s", $type));

        if ($existing) {
            return $this->wpdb->update($this->table_name, $data, ['id' => $existing]);
        } else {
            $data['created_at'] = current_time('mysql');

            return $this->wpdb->insert($this->table_name, $data);
        }
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
        $config_json = $this->wpdb->get_var($this->wpdb->prepare("SELECT config FROM {$this->table_name} WHERE type = %s", $type));

        if (!$config_json) {
            return null;
        }

        $configData = json_decode($config_json, true);

        return FormConfig::fromArray($configData);
    }
}