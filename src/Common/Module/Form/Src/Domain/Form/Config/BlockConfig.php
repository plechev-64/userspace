<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Form\Config;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * DTO для конфигурации блока формы.
 */
class BlockConfig
{
    private array $fields = [];

    public function __construct(
        private readonly string $title
    )
    {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function addField(string $name, array $fieldConfig): void
    {
        $this->fields[$name] = $fieldConfig;
    }

    public function hasField(string $fieldName): bool
    {
        return isset($this->fields[$fieldName]);
    }

    public function updateFieldValue(string $fieldName, mixed $value): void
    {
        if ($this->hasField($fieldName)) {
            $this->fields[$fieldName]['value'] = $value;
        }
    }

    public function removeField(string $fieldName): bool
    {
        if ($this->hasField($fieldName)) {
            unset($this->fields[$fieldName]);
            return true;
        }
        return false;
    }

    public function toArray(): array
    {
        // Приводим поля к нужному формату, чтобы сохранить ключи
        $fieldsArray = [];
        foreach ($this->fields as $name => $fieldData) {
            $fieldsArray[$name] = $fieldData;
        }

        return [
            'title' => $this->title,
            'fields' => $fieldsArray,
        ];
    }
}