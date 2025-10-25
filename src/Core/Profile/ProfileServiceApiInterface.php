<?php

namespace UserSpace\Core\Profile;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Common\Module\User\Src\Domain\UserInterface;

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
     * @param UserInterface|null $user Объект пользователя.
     * @return string|null URL вкладки или null, если страница не настроена.
     */
    public function getTabUrl(string $tabId, ?UserInterface $user = null): ?string;

    /**
     * Определяет и возвращает активную вкладку на основе текущего URL.
     * Если вкладка в URL не указана или не найдена, возвращает вкладку по умолчанию.
     *
     * @return AbstractTab|null Активная вкладка или null, если вкладки не найдены.
     */
    public function getActiveTab(): ?AbstractTab;
}