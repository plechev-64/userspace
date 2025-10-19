<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\FieldDto;

class TextFieldDto extends FieldDto
{
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, 'text', $config);
    }
}