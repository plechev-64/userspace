<?php

namespace UserSpace\Form;

use UserSpace\Form\Field\DTO\FieldDto;

class KeyValueEditorFieldDto extends FieldDto
{
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, 'key_value_editor', $config);
	}

	public function toArray(): array
	{
		return [
			'type'  => $this->type,
			'label' => $this->label,
			'rules' => $this->rules,
		];
	}
}