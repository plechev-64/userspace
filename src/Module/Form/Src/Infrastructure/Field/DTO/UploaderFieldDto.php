<?php

namespace UserSpace\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Module\Form\Src\Domain\Field\DTO\FieldDto;

class UploaderFieldDto extends FieldDto
{
    public bool $multiple = false;

    public function __construct(
        string $name,
        array  $config
    )
    {
        parent::__construct($name, 'uploader', $config);
        $this->multiple = $config['multiple'] ?? false;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'multiple' => $this->multiple,
        ]);
    }
}