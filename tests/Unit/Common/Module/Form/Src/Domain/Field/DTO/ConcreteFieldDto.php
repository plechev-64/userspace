<?php

namespace UserSpace\Tests\Unit\Common\Module\Form\Src\Domain\Field\DTO;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;

class ConcreteFieldDto extends AbstractFieldDto
{
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, 'concrete_type', $config);
    }
}