<?php

namespace UserSpace\Common\Module\Locations\Src\Domain;

/**
 * Базовый интерфейс для всех элементов меню (вкладок и кнопок).
 */
interface ItemInterface
{
    /**
     * Возвращает HTML-представление элемента (например, для меню).
     * @return string
     */
    public function render(): string;
    public function getId(): string;

    public function getTitle(): string;

    public function getLocation(): string;

    public function getOrder(): int;

    public function getParentId(): ?string;

    public function getIcon(): ?string;

    /**
     * Возвращает тип элемента ('tab' или 'button').
     */
    public function getItemType(): string;

    /**
     * Возвращает эндпоинт для действия кнопки.
     */
    public function getActionEndpoint(): ?string;

    /**
     * Проверяет, является ли элемент приватным (виден только владельцу).
     */
    public function isPrivate(): bool;

    /**
     * Проверяет, имеет ли текущий пользователь право на просмотр элемента.
     */
    public function canView(): bool;

    /**
     * Преобразует объект в массив.
     */
    public function toArray(): array;

    public function updateFromArray(array $data): void;
}