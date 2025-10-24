<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Common\Module\User\Src\Domain\UserInterface;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Query\QueryApiInterface;

/**
 * Сервис для определения контекста просматриваемого пользователя.
 *
 * Определяет, чей профиль просматривается в данный момент,
 * и является ли текущий посетитель его владельцем.
 */
class ViewedUserContext
{
    private const OPTION_NAME = 'usp_settings';
    private const DEFAULT_QUERY_VAR = 'user_id';

    private ?UserInterface $viewedUser = null;
    private ?UserInterface $currentUser = null;
    private bool $isProfileRequestedViaQueryVar = false;
    private bool $isInitialized = false;

    public function __construct(
        private readonly OptionManagerInterface $optionManager,
        private readonly UserApiInterface       $userApi,
        private readonly QueryApiInterface      $wpQueryApi,
        private readonly Request                $request
    )
    {
        // Инициализация будет отложена до первого вызова метода.
    }

    /**
     * Возвращает объект просматриваемого пользователя.
     *
     * @return UserInterface|null
     */
    public function getViewedUser(): ?UserInterface
    {
        $this->init();
        return $this->viewedUser;
    }

    /**
     * Возвращает объект текущего авторизованного пользователя.
     *
     * @return UserInterface|null
     */
    public function getCurrentUser(): ?UserInterface
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

        return $this->viewedUser->getId() === $this->currentUser->getId();
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

    /**
     * Инициализирует контекст, определяя текущего и просматриваемого пользователя.
     */
    private function init(): void
    {
        if ($this->isInitialized) {
            return;
        }

        $currentUser = $this->userApi->getCurrentUser();
        if ($currentUser->getId() > 0) {
            $this->currentUser = $currentUser;
        }

        $queryVarName = $this->getQueryVarName();
        $userId = $this->wpQueryApi->getQueryVar($queryVarName);

        if (empty($userId) && $this->request->getQuery($queryVarName) !== null) {
            $userId = (int)$this->request->getQuery($queryVarName);
        }

        if (empty($userId)) {
            // Если ID не передан, считаем, что просматривается профиль текущего пользователя
            $this->isProfileRequestedViaQueryVar = false;
            $this->viewedUser = $this->currentUser;
        } else {
            $user = $this->userApi->getUserBy('ID', (int)$userId);
            if ($user) {
                $this->viewedUser = $user;
                $this->isProfileRequestedViaQueryVar = true;
            }
        }

        $this->isInitialized = true;
    }
}