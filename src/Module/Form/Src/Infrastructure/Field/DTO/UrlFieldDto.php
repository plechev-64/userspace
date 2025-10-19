<?php

namespace UserSpace\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Module\Form\Src\Domain\Field\DTO\FieldDto;

class UrlFieldDto extends FieldDto
{
	public function __construct(string $name, array $config)
	{
		parent::__construct($name, 'url', $config);
	}
}