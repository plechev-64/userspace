<?php

namespace UserSpace\Theme\Minimal\Service;

use UserSpace\Common\Module\Locations\Src\Infrastructure\ItemRenderer;
use UserSpace\Common\Service\AvatarManager;

/**
 * Отвечает за подготовку данных для шаблона темы "Minimal".
 */
class ViewDataProvider
{
    public function __construct(
        private readonly AvatarManager              $avatarManager,
        private readonly ItemRenderer               $itemRenderer,
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
            'avatarBlock'      => $this->avatarManager->renderAvatarBlock(),
            'sidebarMenu'      => $this->itemRenderer->renderLocation( 'sidebar' ),
            'mainContent'      => $this->itemRenderer->renderTabsContent(
                '<div class="usp-account-tab-pane %4$s" id="%1$s" data-content-type="%2$s" data-content-source="%3$s">%5$s</div>'
            ),
        ];
    }
}