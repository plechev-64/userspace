<?php

namespace UserSpace\WpAdapter;

use UserSpace\Core\Auth\AuthApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Адаптер для функций аутентификации WordPress.
 */
class AuthApi implements AuthApiInterface
{
    public function signIn(array $credentials): \WP_User|\WP_Error
    {
        return wp_signon($credentials, is_ssl());
    }

    public function secureSignIn(array $credentials): \WP_User|\WP_Error
    {
        $user = wp_signon($credentials, is_ssl());

        if (!is_wp_error($user)) {
            wp_set_auth_cookie($user->ID, $credentials['remember'] ?? false);
        }

        return $user;
    }

    public function logOut(): void
    {
        wp_logout();
    }
}