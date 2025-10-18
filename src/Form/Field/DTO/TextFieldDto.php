<?php

namespace UserSpace\Form\Field\DTO;

class TextFieldDto extends FieldDto
{
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, 'text', $config);
	}
}