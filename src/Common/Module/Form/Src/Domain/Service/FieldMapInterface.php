<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Service;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;

/**
 * Интерфейс для объекта, представляющего "карту" одного типа поля.
 * Он связывает класс поля, его DTO и класс-рендерер.
 */
interface FieldMapInterface
{
    /**
     * Возвращает полное имя класса поля.
     * @return class-string<FieldInterface>
     */
    public function getFieldClass(): string;

    /**
     * Возвращает полное имя класса DTO для поля.
     * @return class-string<AbstractFieldDto>
     */
    public function getDtoClass(): string;


    /**
     * Возвращает массив с данными карты поля.
     * @return array{class: class-string<FieldInterface>, dto: class-string<AbstractFieldDto>}
     */
    public function toArray(): array;
}