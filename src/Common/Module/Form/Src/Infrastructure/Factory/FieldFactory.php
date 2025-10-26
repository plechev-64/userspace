<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Factory;

use UserSpace\Common\Module\Form\Src\Domain\Factory\FieldFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Service\FieldMapRegistryInterface;
use UserSpace\Core\Container\ContainerInterface;

class FieldFactory implements FieldFactoryInterface
{
    public function __construct(
        private readonly ContainerInterface        $container,
        private readonly FieldMapRegistryInterface $fieldMapper
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function createFromDto(AbstractFieldDto $dto): FieldInterface
    {
        $fieldClassName = $this->fieldMapper->getClass($dto->type);

        /** @var FieldInterface $field */
        $field = $this->container->build($fieldClassName);
        $field->init($dto);

        return $field;
    }

    /**
     * @param string $name
     * @param array $config
     * @return FieldInterface
     */
    public function createFromConfig(string $name, array $config): FieldInterface
    {
        $dtoClass = $this->fieldMapper->getDtoClass($config['type']);
        $dto = new $dtoClass($name, $config);
        return $this->createFromDto($dto);
    }
}