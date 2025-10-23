<?php

namespace UserSpace\Common\Service;

use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\Query\QueryApiInterface;
use UserSpace\Core\User\UserApiInterface;
use WP_User;

/**
 * Сервис для определения контекста просматриваемого пользователя.
 *
 * Определяет, чей профиль просматривается в данный момент,
 * и является ли текущий посетитель его владельцем.
 */
class ViewedUserContext
{
    private ?WP_User $viewedUser = null;
    private ?WP_User $currentUser = null;
    private const OPTION_NAME = 'usp_settings';
    private const DEFAULT_QUERY_VAR = 'user_id';
    private bool $isProfileRequestedViaQueryVar = false;
    private bool $isInitialized = false;

    public function __construct(
        private readonly OptionManagerInterface $optionManager,
        private readonly UserApiInterface       $userApi,
        private readonly QueryApiInterface      $wpQueryApi
    )
    {
        // Инициализация будет отложена до первого вызова метода.
    }

    /**
     * Инициализирует контекст, определяя текущего и просматриваемого пользователя.
     */
    private function init(): void
    {
        if ($this->isInitialized) return;

        $this->currentUser = $this->userApi->getCurrentUser();
        if (!$this->currentUser->ID) {
            $this->currentUser = null;
        }

        $queryVarName = $this->getQueryVarName();
        $userId = $this->wpQueryApi->getQueryVar($queryVarName);

        if (empty($userId) && isset($_GET[$queryVarName])) {
            $userId = (int)$_GET[$queryVarName];
        }

        if (empty($userId)) {
            // Если ID не передан, считаем, что просматривается профиль текущего пользователя
            $this->isProfileRequestedViaQueryVar = false;
            $this->viewedUser = $this->currentUser;
            return;
        }

        $user = $this->userApi->getUserBy('ID', (int)$userId);
        if ($user) {
            $this->viewedUser = $user;
            $this->isProfileRequestedViaQueryVar = true;
        }

        $this->isInitialized = true;
    }

    /**
     * Возвращает объект просматриваемого пользователя.
     *
     * @return WP_User|null
     */
    public function getViewedUser(): ?WP_User
    {
        $this->init();
        return $this->viewedUser;
    }

    /**
     * Возвращает объект текущего авторизованного пользователя.
     *
     * @return WP_User|null
     */
    public function getCurrentUser(): ?WP_User
    {
        $this->init();
        return $this->currentUser;
    }

    /**
     * Проверяет, является ли текущий посетитель владельцем просматриваемого профиля.
     *
     * @return bool
     */
    public function isOwner(): bool
    {
        $this->init();
        if (!$this->viewedUser || !$this->currentUser) {
            return false;
        }

        return $this->viewedUser->ID === $this->currentUser->ID;
    }

    /**
     * Проверяет, был ли запрошен профиль через GET-параметр в URL.
     *
     * @return bool
     */
    public function isProfileRequestedViaQueryVar(): bool
    {
        $this->init();
        return $this->isProfileRequestedViaQueryVar;
    }

    /**
     * Получает имя GET-параметра для идентификации пользователя из настроек.
     *
     * @return string
     */
    private function getQueryVarName(): string
    {
        $options = $this->optionManager->get(self::OPTION_NAME, []);

        return $options['profile_user_query_var'] ?? self::DEFAULT_QUERY_VAR;
    }
}