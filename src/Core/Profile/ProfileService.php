<?php

namespace UserSpace\Core\Profile;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Common\Module\Locations\Src\Domain\ItemInterface;
use UserSpace\Common\Module\Locations\Src\Domain\ItemManagerInterface;
use UserSpace\Common\Module\Settings\App\SettingsEnum;
use UserSpace\Common\Module\Settings\Src\Domain\PluginSettingsInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Common\Module\User\Src\Domain\UserInterface;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\Sanitizer\SanitizerRule;

class ProfileService implements ProfileServiceApiInterface
{
    public function __construct(
        private readonly PluginSettingsInterface $optionManager,
        private readonly UserApiInterface        $userApi,
        private readonly ItemManagerInterface    $tabManager,
        private readonly Request                 $request,
        private readonly SanitizerInterface      $sanitizer
    )
    {
    }

    public function getProfileUrl(?int $userId = null): ?string
    {

        $pageId = $this->optionManager->get(SettingsEnum::PROFILE_PAGE_ID);

        if (empty($pageId)) {
            return null;
        }

        $userToUse = $userId ?? $this->userApi->getCurrentUser()?->getId();
        if (!$userToUse) {
            return get_permalink($pageId);
        }

        $userQueryVar = $this->optionManager->get(SettingsEnum::PROFILE_USER_QUERY_VAR, 'user_id');

        return add_query_arg([$userQueryVar => $userId], get_permalink($pageId));
    }

    public function getTabUrl(string $tabId, ?UserInterface $user = null): ?string
    {
        $baseUrl = $this->getProfileUrl($user?->getId());
        if ($baseUrl === null) {
            return null;
        }

        $tabQueryVar = $this->optionManager->get(SettingsEnum::PROFILE_TAB_QUERY_VAR, 'tab');

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

        $tabQueryVar = $this->optionManager->get(SettingsEnum::PROFILE_TAB_QUERY_VAR, 'tab');
        $tabQueryId = $this->request->getQuery($tabQueryVar, '');
        $activeTabId = $this->sanitizer->sanitize(['id' => $tabQueryId], ['id' => SanitizerRule::SLUG])->get('id');
        if (!empty($activeTabId)) {
            $tab = $this->tabManager->getItem($activeTabId);
            if ($tab instanceof AbstractTab) {
                return $tab;
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
}