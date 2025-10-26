<?php

namespace UserSpace\Common\Module\Locations\Src\Domain;

/**
 * Интерфейс для сервиса-регистратора элементов меню (вкладок и кнопок).
 */
interface ItemRegistryInterface
{
    /**
     * Регистрирует новый класс элемента меню.
     *
     * @param class-string<ItemInterface> $itemClassName
     */
    public function registerItem(string $itemClassName): void;

    /**
     * Возвращает все зарегистрированные классы элементов.
     *
     * @return array<int, class-string<ItemInterface>>
     */
    public function getRegisteredItems(): array;
}