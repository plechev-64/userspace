<?php

namespace UserSpace\Module\Form\Src\Domain\Field\DTO;

abstract class FieldDto
{
    public string $name;
    public string $label;
    public string $type;
    public $value = null;
    public array $rules = [];
    public array $attributes = [];

    public function __construct(
        string $name,
        string $type,
        array  $config
    )
    {
        $this->name = $name;
        $this->type = $type;
        $this->label = $config['label'] ?? '';
        $this->value = $config['value'] ?? null;
        $this->rules = $config['rules'] ?? [];
        $this->attributes = $config['attributes'] ?? [];
    }

    /**
     * Преобразует DTO в массив для конфигурации.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'rules' => $this->rules,
            'value' => $this->value,
            'name' => $this->name,
            'attributes' => $this->attributes,
        ];
    }
}