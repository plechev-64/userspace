<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;

class NumberFieldDto extends AbstractFieldDto
{
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, 'number', $config);
    }
}