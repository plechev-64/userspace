<?php

namespace UserSpace\Core\User;

use UserSpace\Core\Auth\AuthApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для взаимодействия с функциями WordPress, связанными с пользователями.
 * Абстрагирует get_user_meta, update_user_meta, current_user_can и т.д.
 */
interface UserApiInterface
{
    /**
     * Возвращает сервис для работы с аутентификацией.
     *
     * @return AuthApiInterface
     */
    public function auth(): AuthApiInterface;

    /**
     * Создает нового пользователя.
     * Обертка для wp_create_user().
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return int|\WP_Error
     */
    public function createUser(string $username, string $password, string $email = ''): int|\WP_Error;

    /**
     * Проверяет, обладает ли текущий пользователь указанным правом.
     * Обертка для current_user_can().
     *
     * @param string $capability Право для проверки.
     * @param mixed ...$args Дополнительные аргументы (например, ID поста или пользователя).
     * @return bool
     */
    public function currentUserCan(string $capability, ...$args): bool;

    /**
     * Возвращает объект текущего пользователя.
     * Обертка для wp_get_current_user().
     *
     * @return \WP_User
     */
    public function getCurrentUser(): \WP_User;

    /**
     * Получает мета-данные пользователя.
     * Обертка для get_user_meta().
     *
     * @param int $userId ID пользователя.
     * @param string $key Ключ мета-поля.
     * @param bool $single Возвращать ли одно значение.
     * @return mixed
     */
    public function getUserMeta(int $userId, string $key = '', bool $single = false): mixed;

    /**
     * Возвращает ID текущего авторизованного пользователя.
     * Обертка для get_current_user_id().
     *
     * @return int
     */
    public function getCurrentUserId(): int;

    /**
     * Обновляет мета-данные пользователя.
     * Обертка для update_user_meta().
     *
     * @param int $userId ID пользователя.
     * @param string $metaKey Ключ мета-поля.
     * @param mixed $metaValue Новое значение.
     * @return int|bool Meta ID в случае успеха, false в случае ошибки.
     */
    public function updateUserMeta(int $userId, string $metaKey, mixed $metaValue): int|bool;

    /**
     * Получает данные пользователя по указанному полю и значению.
     * Обертка для get_user_by().
     *
     * @param string $field Поле для поиска ('id', 'ID', 'slug', 'email', 'login').
     * @param string|int $value Значение для поиска.
     * @return \WP_User|false
     */
    public function getUserBy(string $field, string|int $value): \WP_User|false;

    /**
     * Вставляет пользователя в базу данных.
     * Обертка для wp_insert_user().
     *
     * @param array<string, mixed> $userData
     * @return int|\WP_Error
     */
    public function insertUser(array $userData): int|\WP_Error;

    /**
     * Обновляет данные пользователя в базе данных.
     * Обертка для wp_update_user().
     *
     * @param array<string, mixed> $userData
     * @return int|\WP_Error
     */
    public function updateUser(array $userData): int|\WP_Error;

    /**
     * Проверяет, авторизован ли текущий пользователь.
     * Обертка для is_user_logged_in().
     *
     * @return bool
     */
    public function isUserLoggedIn(): bool;
}