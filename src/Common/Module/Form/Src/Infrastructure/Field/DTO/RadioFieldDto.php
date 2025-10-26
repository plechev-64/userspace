<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;

class RadioFieldDto extends AbstractFieldDto
{
    public array $options = [];

    public function __construct(string $name, array $config)
    {
        parent::__construct($name, 'radio', $config);
        $this->options = $config['options'] ?? [];
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->options,
        ]);
    }
}