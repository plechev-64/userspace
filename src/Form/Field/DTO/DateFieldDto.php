<?php

namespace UserSpace\Form\Field\DTO;

class DateFieldDto extends FieldDto
{
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, 'date', $config);
	}
}