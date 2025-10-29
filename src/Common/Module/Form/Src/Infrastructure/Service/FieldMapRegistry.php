<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Service;

use InvalidArgumentException;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Service\FieldMapRegistryInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Карта соответствия типов полей их классам-реализациям.
 */
class FieldMapRegistry implements FieldMapRegistryInterface
{
    /**
     * @var array<string, FieldMap>
     */
    private array $map = [];

    /**
     * @param array<string, FieldMap> $fieldMaps
     */
    public function __construct(array $fieldMaps = [])
    {
        $this->map = $fieldMaps;
    }

    /**
     * Возвращает имя класса для указанного типа поля.
     *
     * @param string $type Тип поля.
     *
     * @return class-string<FieldInterface>
     * @throws InvalidArgumentException Если тип поля не найден.
     */
    public function getClass(string $type): string
    {
        if (!$this->has($type)) {
            throw new InvalidArgumentException("Тип поля '{$type}' не поддерживается.");
        }

        return $this->map[$type]->getFieldClass();
    }

    /**
     * Возвращает имя DTO-класса для указанного типа поля.
     *
     * @param string $type
     *
     * @return class-string<AbstractFieldDto>
     * @throws InvalidArgumentException
     */
    public function getDtoClass(string $type): string
    {
        if (!$this->has($type)) {
            throw new InvalidArgumentException("Тип поля '{$type}' не поддерживается.");
        }

        return $this->map[$type]->getDtoClass();
    }

    /**
     * Проверяет, существует ли реализация для указанного типа поля.
     *
     * @param string $type Тип поля.
     *
     * @return bool
     */
    public function has(string $type): bool
    {
        return isset($this->map[$type]);
    }

    /**
     * Возвращает всю карту полей.
     * @return array<string, FieldMap>
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * Возвращает всю карту полей в виде массива.
     * @return array<string, array{class: class-string<FieldInterface>, dto: class-string<AbstractFieldDto>}>
     */
    public function toArray(): array
    {
        return array_map(function ($fieldMap) {
            return $fieldMap->toArray();
        }, $this->map);
    }

    public function register(string $type, FieldMap $fieldMap): void
    {
        $this->map[$type] = $fieldMap;
    }
}