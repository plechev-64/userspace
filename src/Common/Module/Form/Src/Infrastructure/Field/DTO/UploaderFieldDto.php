<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;

class UploaderFieldDto extends AbstractFieldDto
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