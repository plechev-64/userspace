<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Factory;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;

/**
 * Интерфейс для фабрики, создающей объекты полей.
 */
interface FieldFactoryInterface
{
    /**
     * @param AbstractFieldDto $dto
     * @return FieldInterface
     */
    public function createFromDto(AbstractFieldDto $dto): FieldInterface;

    /**
     * @param string $name
     * @param array $config
     * @return FieldInterface
     */
    public function createFromConfig(string $name, array $config): FieldInterface;
}