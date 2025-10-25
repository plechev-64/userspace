<?php

namespace UserSpace\Common\Module\Locations\Src\Domain;

/**
 * Интерфейс для сервиса, который управляет элементами меню (вкладками и кнопками).
 */
interface ItemManagerInterface
{
    /**
     * Регистрирует элемент меню (вкладку или кнопку) по имени его класса.
     *
     * @param class-string<ItemInterface> $itemClassName Имя класса элемента.
     * @param array|null $configData Данные из конфигурации для обновления элемента.
     *
     * @throws \Exception
     */
    public function loadItem(string $itemClassName, ?array $configData = null): void;

    /**
     * Возвращает элемент меню по его ID.
     *
     * @param string $id
     * @return ItemInterface|null
     */
    public function getItem(string $id): ?ItemInterface;

    /**
     * Возвращает отсортированный и отфильтрованный по правам доступа массив элементов меню для указанной локации.
     *
     * @param string|null $location
     * @return ItemInterface[]
     */
    public function getItems(?string $location = null): array;

    /**
     * Возвращает все зарегистрированные элементы в виде иерархии или плоского списка без фильтрации прав.
     *
     * @param bool $flat Вернуть плоский список вместо иерархии.
     * @return ItemInterface[]
     */
    public function getAllRegisteredItems(bool $flat = false): array;
}