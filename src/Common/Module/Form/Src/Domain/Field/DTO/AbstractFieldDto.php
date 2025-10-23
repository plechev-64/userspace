<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Field\DTO;

abstract class AbstractFieldDto
{
    public string $name;
    public string $type;
    public string $label;
    public mixed $value = null;
    public ?string $description = null;
    public ?array $dependency = null;
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
        $this->description = $config['description'] ?? null;
        $this->dependency = $config['dependency'] ?? null;
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
            'name' => $this->name,
            'type' => $this->type,
            'label' => $this->label,
            'value' => $this->value,
            'description' => $this->description,
            'dependency' => $this->dependency,
            'rules' => $this->rules,
            'attributes' => $this->attributes,
        ];
    }
}