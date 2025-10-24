<?php

namespace UserSpace\Common\Module\Tabs\Src\Domain;

/**
 * Базовый интерфейс для всех элементов меню (вкладок и кнопок).
 */
interface ItemInterface
{
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
     * Возвращает HTML-содержимое для вкладки.
     */
    public function getContent(): string;

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
}