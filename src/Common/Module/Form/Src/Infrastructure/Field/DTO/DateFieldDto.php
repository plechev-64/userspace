<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;

class DateFieldDto extends AbstractFieldDto
{
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, 'date', $config);
    }
}