<?php

namespace UserSpace\Form\Field\DTO;

class SelectFieldDto extends FieldDto
{
    public array $options = [];

    public function __construct(
        string $name,
        array $config
    ) {
        parent::__construct( $name, 'select', $config );
        $this->options = $config['options'] ?? [];
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->options,
        ]);
    }
}