<?php

namespace UserSpace\Core\Profile;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Common\Module\Locations\Src\Domain\ItemInterface;
use UserSpace\Common\Module\Locations\Src\Domain\ItemManagerInterface;
use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Common\Module\User\Src\Domain\UserInterface;
use UserSpace\Core\Http\Request;

class ProfileService implements ProfileServiceApiInterface
{
    private const OPTION_NAME = 'usp_settings';

    private ?array $settings = null;

    public function __construct(
        private readonly OptionManagerInterface $optionManager,
        private readonly UserApiInterface       $userApi,
        private readonly ItemManagerInterface   $tabManager,
        private readonly Request                $request
    )
    {
    }

    public function getProfileUrl(?int $userId = null): ?string
    {
        $settings = $this->_getSettings();
        $pageId = $settings['profile_page_id'] ?? null;

        if (empty($pageId)) {
            return null;
        }

        $userToUse = $userId ?? $this->userApi->getCurrentUser()?->getId();
        if (!$userToUse) {
            return get_permalink($pageId);
        }

        $userQueryVar = !empty($settings['profile_user_query_var']) ? $settings['profile_user_query_var'] : 'user_id';

        return add_query_arg([$userQueryVar => $userId], get_permalink($pageId));
    }

    public function getTabUrl(string $tabId, ?UserInterface $user = null): ?string
    {
        $baseUrl = $this->getProfileUrl($user?->getId());
        if ($baseUrl === null) {
            return null;
        }

        $settings = $this->_getSettings();
        $tabQueryVar = !empty($settings['profile_tab_query_var']) ? $settings['profile_tab_query_var'] : 'tab';

        return add_query_arg([$tabQueryVar => $tabId], $baseUrl);
    }

    public function getActiveTab(): ?AbstractTab
    {
        $items = $this->tabManager->getItems();
        if (empty($items)) {
            return null;
        }

        // Для определения активной вкладки нам нужны только элементы типа "tab"
        /** @var AbstractTab[] $tabs */
        $tabs = array_filter($items, static fn(ItemInterface $item): bool => $item instanceof AbstractTab);
        if (empty($tabs)) {
            return null; // Нет доступных вкладок
        }

        $settings = $this->_getSettings();
        $tabQueryVar = !empty($settings['profile_tab_query_var']) ? $settings['profile_tab_query_var'] : 'tab';

        $activeTabId = sanitize_text_field($this->request->getQuery($tabQueryVar, ''));

        if (!empty($activeTabId)) {
            foreach ($tabs as $tabItem) {
                if ($tabItem->getId() === $activeTabId) {
                    return $tabItem;
                }
            }
        }

        // Если активная вкладка не найдена или не задана, возвращаем вкладку по умолчанию
        return $this->_getDefaultTab($tabs);
    }

    /**
     * @param AbstractTab[] $tabs
     * @return AbstractTab|null
     */
    private function _getDefaultTab(array $tabs): ?AbstractTab
    {
        // Сначала ищем вкладку, явно помеченную как по умолчанию
        foreach ($tabs as $tab) {
            if ($tab->isDefault()) {
                return $tab; // Этот метод есть только у AbstractTab
            }
        }

        // Если такой нет, возвращаем первую из списка
        if (!empty($tabs)) {
            return reset($tabs);
        }

        return null;
    }

    /**
     * Получает и кэширует настройки плагина.
     *
     * @return array
     */
    private function _getSettings(): array
    {
        if ($this->settings === null) {
            $this->settings = $this->optionManager->get(self::OPTION_NAME, []);
        }
        return $this->settings;
    }
}