<?php

namespace UserSpace\Core\Profile;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\User\UserApiInterface;
use WP_User;

class ProfileService implements ProfileServiceApiInterface
{
    private const OPTION_NAME = 'usp_settings';

    private ?array $settings = null;

    public function __construct(
        private readonly OptionManagerInterface $optionManager,
        private readonly UserApiInterface $userApi,
        private readonly TabManager $tabManager,
        private readonly Request $request
    ) {
    }

    public function getProfileUrl(?int $userId = null): ?string
    {
        $settings = $this->getSettings();
        $pageId = $settings['profile_page_id'] ?? null;

        if (empty($pageId)) {
            return null;
        }

        $userToUse = $userId ?? $this->userApi->getCurrentUser()->getWPUser()->ID;
        if (!$userToUse) {
            return get_permalink($pageId);
        }

        $userQueryVar = !empty($settings['profile_user_query_var']) ? $settings['profile_user_query_var'] : 'user_id';

        return add_query_arg([$userQueryVar => $userId], get_permalink($pageId));
    }

    public function getTabUrl(string $tabId, ?WP_User $user = null): ?string
    {
        $baseUrl = $this->getProfileUrl($user->ID);
        if ($baseUrl === null) {
            return null;
        }

        $settings = $this->getSettings();
        $tabQueryVar = !empty($settings['profile_tab_query_var']) ? $settings['profile_tab_query_var'] : 'tab';

        return add_query_arg([$tabQueryVar => $tabId], $baseUrl);
    }

    public function getActiveTab(): ?AbstractTab
    {
        $tabs = $this->tabManager->getTabs();
        if (empty($tabs)) {
            return null;
        }

        $settings = $this->getSettings();
        $tabQueryVar = !empty($settings['profile_tab_query_var']) ? $settings['profile_tab_query_var'] : 'tab';

        $activeTabId = sanitize_text_field($this->request->getQuery($tabQueryVar, ''));

        if (!empty($activeTabId)) {
            foreach ($tabs as $tab) {
                if ($tab->getId() === $activeTabId) {
                    return $tab;
                }
            }
        }

        // Если активная вкладка не найдена или не задана, возвращаем вкладку по умолчанию
        return $this->getDefaultTab($tabs);
    }

    /**
     * @param AbstractTab[] $tabs
     * @return AbstractTab|null
     */
    private function getDefaultTab(array $tabs): ?AbstractTab
    {
        // Сначала ищем вкладку, явно помеченную как по умолчанию
        foreach ($tabs as $tab) {
            if ($tab->isDefault()) {
                return $tab;
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
    private function getSettings(): array
    {
        if ($this->settings === null) {
            $this->settings = $this->optionManager->get(self::OPTION_NAME, []);
        }
        return $this->settings;
    }
}