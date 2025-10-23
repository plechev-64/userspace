<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;

class KeyValueEditorAbstractFieldDto extends AbstractFieldDto
{
    public function __construct(string $name, array $config)
    {
        parent::__construct($name, 'key_value_editor', $config);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'rules' => $this->rules,
        ];
    }
}