<?php

namespace UserSpace\Adapters;

use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Common\Module\User\Src\Domain\UserInterface;
use UserSpace\Core\Auth\AuthApiInterface;
use UserSpace\Core\Database\DatabaseConnectionInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Адаптер для функций WordPress, связанных с пользователями.
 */
class UserApi implements UserApiInterface
{
    public function __construct(
        private readonly AuthApiInterface            $authApi,
        private readonly DatabaseConnectionInterface $db
    )
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

    public function getCurrentUser(): UserInterface
    {
        return new User(wp_get_current_user());
    }

    public function getUserBy(string $field, int|string $value): UserInterface|false
    {
        $wpUser = get_user_by($field, $value);
        if (!$wpUser) {
            return false;
        }
        return new User($wpUser);
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

    public function deleteMetaFromAllUsers(string $metaKey): bool
    {
        $tableName = $this->db->getUsermetaTableName();

        $result = $this->db->query(
            "DELETE FROM {$tableName} WHERE meta_key = %s",
            $metaKey
        );

        return $result !== false;
    }

    public function getUserdata(int $userId): UserInterface|false
    {
        $wpUser = get_userdata($userId);
        if (!$wpUser) {
            return false;
        }
        return new User($wpUser);
    }
}