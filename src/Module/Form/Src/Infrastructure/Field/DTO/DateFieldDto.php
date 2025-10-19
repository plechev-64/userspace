<?php

namespace UserSpace\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Module\Form\Src\Domain\Field\DTO\FieldDto;

class DateFieldDto extends FieldDto
{
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, 'date', $config);
	}
}