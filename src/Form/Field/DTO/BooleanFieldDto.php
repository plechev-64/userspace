<?php

namespace UserSpace\Form\Field\DTO;

class BooleanFieldDto extends FieldDto
{
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, 'boolean', $config);
	}
}