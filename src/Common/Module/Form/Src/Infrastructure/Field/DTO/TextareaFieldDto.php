<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;

class TextareaFieldDto extends AbstractFieldDto
{
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, 'textarea', $config);
    }
}