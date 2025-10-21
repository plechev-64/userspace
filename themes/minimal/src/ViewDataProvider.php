<?php

namespace UserSpace\Theme\Minimal;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabRenderer;
use UserSpace\Common\Service\AvatarManager;
use UserSpace\Common\Service\TemplateManagerInterface;

/**
 * Отвечает за подготовку данных для шаблона темы "Minimal".
 */
class ViewDataProvider
{
    public function __construct(
        private readonly AvatarManager $avatarManager,
        private readonly TabRenderer $tabRenderer,
        private readonly TabManager $tabManager,
        private readonly TemplateManagerInterface $templateManager
    ) {
    }

    /**
     * Собирает и возвращает данные для рендеринга шаблона.
     *
     * @return array<string, string>
     */
    public function prepareTemplateData(): array
    {
        return [
            'avatarBlock' => $this->avatarManager->renderAvatarBlock(),
            'sidebarMenu' => $this->renderMenu('sidebar', true),
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
     * @param bool   $activateFirst Сделать ли первую вкладку в меню активной.
     *
     * @return string Сгенерированный HTML-код меню.
     */
    private function renderMenu(string $location, bool $activateFirst = false): string
    {
        $tabsToRender = $this->tabManager->getTabs($location);

        if (empty($tabsToRender)) {
            return '';
        }

        return $this->templateManager->render('tab_menu', [
            'tabs_to_render' => $tabsToRender,
            'activate_first' => $activateFirst,
            'location'       => $location,
        ]);
    }
}