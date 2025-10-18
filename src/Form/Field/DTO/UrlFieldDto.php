<?php

namespace UserSpace\Form\Field\DTO;

class UrlFieldDto extends FieldDto
{
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, 'url', $config);
	}
}