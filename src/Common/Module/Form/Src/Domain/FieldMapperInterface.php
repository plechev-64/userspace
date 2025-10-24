<?php

namespace UserSpace\Common\Module\Form\Src\Domain;

use InvalidArgumentException;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;

/**
 * Интерфейс для карты соответствия типов полей их классам-реализациям.
 */
interface FieldMapperInterface
{
    /**
     * Возвращает имя класса для указанного типа поля.
     *
     * @param string $type Тип поля.
     *
     * @return class-string<FieldInterface>
     * @throws InvalidArgumentException Если тип поля не найден.
     */
    public function getClass(string $type): string;

    /**
     * Возвращает имя DTO-класса для указанного типа поля.
     *
     * @param string $type
     *
     * @return class-string<AbstractFieldDto>
     * @throws InvalidArgumentException
     */
    public function getDtoClass(string $type): string;

    /**
     * Проверяет, существует ли реализация для указанного типа поля.
     *
     * @param string $type Тип поля.
     *
     * @return bool
     */
    public function has(string $type): bool;

    /**
     * Возвращает всю карту полей.
     * @return array<string, array{class: class-string<FieldInterface>, dto: class-string<AbstractFieldDto>}>
     */
    public function getMap(): array;
}