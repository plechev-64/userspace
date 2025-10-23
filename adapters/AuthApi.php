<?php

namespace UserSpace\Adapters;

use UserSpace\Common\Module\User\Src\Domain\UserInterface;
use UserSpace\Core\Auth\AuthApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Адаптер для функций аутентификации WordPress.
 */
class AuthApi implements AuthApiInterface
{
    public function signIn(array $credentials): UserInterface|\WP_Error
    {
        $user = wp_signon($credentials, is_ssl());

        if (is_wp_error($user)) {
            return $user;
        }

        return new User($user);
    }

    public function secureSignIn(array $credentials): UserInterface|\WP_Error
    {
        $user = wp_signon($credentials, is_ssl());

        if (!is_wp_error($user)) {
            wp_set_auth_cookie($user->ID, $credentials['remember'] ?? false);
            return new User($user);
        }

        return $user;
    }

    public function logOut(): void
    {
        wp_logout();
    }

    public function retrievePassword(string $userLogin): bool|\WP_Error
    {
        return retrieve_password($userLogin);
    }
}