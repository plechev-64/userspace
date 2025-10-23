<?php

namespace UserSpace\Common\Module\User\Src\Domain;

/**
 * Интерфейс для адаптера пользователя, который оборачивает объект WP_User.
 */
interface UserInterface
{
    /**
     * Возвращает ID пользователя.
     */
    public function getId(): int;

    /**
     * Возвращает логин пользователя.
     */
    public function getLogin(): string;

    /**
     * Возвращает отображаемое имя пользователя.
     */
    public function getDisplayName(): string;

    /**
     * Возвращает email пользователя.
     */
    public function getEmail(): string;

    /**
     * Возвращает массив ролей пользователя.
     * @return string[]
     */
    public function getRoles(): array;

    /**
     * Проверяет, есть ли у пользователя указанная роль.
     */
    public function hasRole(string $role): bool;
}