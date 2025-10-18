<?php

namespace UserSpace\Form\Field\DTO;

class TextareaFieldDto extends FieldDto
{
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, 'textarea', $config);
	}
}