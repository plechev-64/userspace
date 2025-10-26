<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Service;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Service\FieldMapInterface;

class FieldMap implements FieldMapInterface
{
    /**
     * @param class-string<FieldInterface> $fieldClass
     * @param class-string<AbstractFieldDto> $dtoClass
     */
    public function __construct(
        private readonly string $fieldClass,
        private readonly string $dtoClass
    )
    {
    }

    public function getFieldClass(): string
    {
        return $this->fieldClass;
    }

    public function getDtoClass(): string
    {
        return $this->dtoClass;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return [
            'class' => $this->fieldClass,
            'dto' => $this->dtoClass,
        ];
    }
}