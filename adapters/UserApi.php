<?php

namespace UserSpace\Adapters;

use UserSpace\Core\Auth\AuthApiInterface;
use UserSpace\Core\User\UserApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Адаптер для функций WordPress, связанных с пользователями.
 */
class UserApi implements UserApiInterface
{
    public function __construct(private readonly AuthApiInterface $authApi)
    {
    }

    public function auth(): AuthApiInterface
    {
        return $this->authApi;
    }

    public function createUser(string $username, string $password, string $email = ''): int|\WP_Error
    {
        return wp_create_user($username, $password, $email);
    }

    public function currentUserCan(string $capability, ...$args): bool
    {
        return current_user_can($capability, ...$args);
    }

    public function getCurrentUser(): \WP_User
    {
        return wp_get_current_user();
    }

    public function getUserBy(string $field, int|string $value): \WP_User|false
    {
        return get_user_by($field, $value);
    }

    public function getCurrentUserId(): int
    {
        return get_current_user_id();
    }

    public function getUserMeta(int $userId, string $key = '', bool $single = false): mixed
    {
        return get_user_meta($userId, $key, $single);
    }

    public function insertUser(array $userData): int|\WP_Error
    {
        return wp_insert_user($userData);
    }

    public function isUserLoggedIn(): bool
    {
        return is_user_logged_in();
    }

    public function updateUser(array $userData): int|\WP_Error
    {
        return wp_update_user($userData);
    }

    public function updateUserMeta(int $userId, string $metaKey, mixed $metaValue): int|bool
    {
        return update_user_meta($userId, $metaKey, $metaValue);
    }
}