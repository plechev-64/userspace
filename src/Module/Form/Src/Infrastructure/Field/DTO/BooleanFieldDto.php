<?php

namespace UserSpace\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Module\Form\Src\Domain\Field\DTO\FieldDto;

class BooleanFieldDto extends FieldDto
{
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, 'boolean', $config);
	}
}