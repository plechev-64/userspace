<?php

namespace UserSpace\Core\Auth;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для взаимодействия с функциями аутентификации WordPress.
 */
interface AuthApiInterface
{
    /**
     * Авторизует пользователя.
     * Обертка для wp_signon().
     *
     * @param array $credentials Данные для входа.
     * @return \WP_User|\WP_Error
     */
    public function signIn(array $credentials): \WP_User|\WP_Error;

    /**
     * Авторизует пользователя и устанавливает auth cookie.
     *
     * @param array $credentials Данные для входа.
     * @return \WP_User|\WP_Error
     */
    public function secureSignIn(array $credentials): \WP_User|\WP_Error;

    /**
     * Выход текущего пользователя из системы.
     * Обертка для wp_logout().
     */
    public function logOut(): void;

    /**
     * Обрабатывает запрос на восстановление пароля.
     * Обертка для retrieve_password().
     *
     * @param string $userLogin Имя пользователя или email.
     * @return bool|\WP_Error True в случае успеха, WP_Error в случае ошибки.
     */
    public function retrievePassword(string $userLogin): bool|\WP_Error;
}