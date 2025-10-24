<?php

namespace UserSpace\Theme\Minimal\Service;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabRenderer;
use UserSpace\Common\Service\AvatarManager;
use UserSpace\Core\Profile\ProfileServiceApiInterface;
use UserSpace\Core\TemplateManagerInterface;

/**
 * Отвечает за подготовку данных для шаблона темы "Minimal".
 */
class ViewDataProvider
{
    public function __construct(
        private readonly AvatarManager              $avatarManager,
        private readonly TabRenderer                $tabRenderer,
        private readonly TabManager                 $tabManager,
        private readonly TemplateManagerInterface   $templateManager,
        private readonly ProfileServiceApiInterface $profileService
    )
    {
    }

    /**
     * Собирает и возвращает данные для рендеринга шаблона.
     *
     * @return array<string, string>
     */
    public function prepareTemplateData(): array
    {
        $activeTab = $this->profileService->getActiveTab();
        $activeTabId = $activeTab ? $activeTab->getId() : null;

        return [
            'avatarBlock' => $this->avatarManager->renderAvatarBlock(),
            'sidebarMenu' => $this->renderMenu('sidebar', $activeTabId),
            'tabsContent' => $this->tabRenderer->renderTabsContent(
                '<div class="usp-account-tab-pane %4$s" id="%1$s" data-content-type="%2$s" data-content-source="%3$s">%5$s</div>',
                'sidebar'
            ),
        ];
    }

    /**
     * Рендерит меню вкладок для указанной локации.
     *
     * @param string $location Идентификатор локации ('sidebar').
     * @param string|null $activeTabId ID активной вкладки.
     *
     * @return string Сгенерированный HTML-код меню.
     */
    private function renderMenu(string $location, ?string $activeTabId): string
    {
        $tabsToRender = $this->tabManager->getTabs($location);

        if (empty($tabsToRender)) {
            return '';
        }

        return $this->templateManager->render('tab_menu', [
            'tabs_to_render' => $tabsToRender,
            'location' => $location,
            'active_tab_id' => $activeTabId,
        ]);
    }
}