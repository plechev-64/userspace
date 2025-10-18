<?php

namespace UserSpace\Form\Field\DTO;

class CheckboxFieldDto extends FieldDto
{
    public array $options = [];
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, 'checkbox', $config);
        $this->options = $config['options'] ?? [];
	}

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->options,
        ]);
    }
}