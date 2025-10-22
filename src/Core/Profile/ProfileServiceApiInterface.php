<?php

namespace UserSpace\Core\Profile;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;
use WP_User;

/**
 * API для управления логикой страницы профиля пользователя.
 */
interface ProfileServiceApiInterface
{
    /**
     * Возвращает URL страницы профиля для указанного пользователя.
     * Если пользователь не указан, используется текущий залогиненный пользователь.
     *
     * @param int|null $userId
     * @return string|null URL страницы профиля или null, если страница не настроена.
     */
    public function getProfileUrl(?int $userId = null): ?string;

    /**
     * Возвращает URL для конкретной вкладки на странице профиля.
     *
     * @param string $tabId Идентификатор вкладки.
     * @param WP_User|null $user Объект пользователя.
     * @return string|null URL вкладки или null, если страница не настроена.
     */
    public function getTabUrl(string $tabId, ?WP_User $user = null): ?string;

    /**
     * Определяет и возвращает активную вкладку на основе текущего URL.
     * Если вкладка в URL не указана или не найдена, возвращает вкладку по умолчанию.
     *
     * @return AbstractTab|null Активная вкладка или null, если вкладки не найдены.
     */
    public function getActiveTab(): ?AbstractTab;
}