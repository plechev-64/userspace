<?php

namespace UserSpace\Common\Module\Locations\Src\Infrastructure;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Common\Module\Locations\Src\Domain\ItemInterface;
use UserSpace\Common\Module\Locations\Src\Domain\ItemManagerInterface;
use UserSpace\Common\Service\ViewedUserContext;
use UserSpace\Core\Profile\ProfileServiceApiInterface;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Сервис для рендеринга контента вкладки на основе ее contentSource.
 */
class ItemRenderer
{
    public function __construct(
        private readonly ItemManagerInterface       $tabManager,
        private readonly ViewedUserContext          $viewedUserContext,
        private readonly StringFilterInterface      $stringFilter,
        private readonly ProfileServiceApiInterface $profileService
    )
    {
    }

    /**
     * Рендерит HTML для всех панелей контента вкладок.
     *
     * @param string $paneWrapperHtml HTML-шаблон для обертки одной панели, использующий плейсхолдеры sprintf.
     * @param string|null $mainLocation Идентификатор локации, чья первая вкладка должна быть активна по умолчанию.
     *
     * @return string
     * @throws \Exception
     */
    public function renderTabsContent(string $paneWrapperHtml, ?string $mainLocation = null): string
    {
        $output = '';
        $viewedUser = $this->viewedUserContext->getViewedUser();
        if (!$viewedUser) {
            return '';
        }

        // Определяем ID активной вкладки с помощью ProfileService
        $activeTab = $this->profileService->getActiveTab();
        $activeTabId = $activeTab?->getId();

        // Получаем плоский список всех зарегистрированных вкладок
        $flatItems = $this->tabManager->getAllRegisteredItems(true);

        foreach ($flatItems as $item) {
            // Контентные панели рендерятся только для реальных вкладок, а не для кнопок.
            if (!$item instanceof AbstractTab) {
                continue;
            }

            // Не рендерим контент-блок для "чистой" родительской вкладки,
            // так как ее роль - быть контейнером для подменю в навигации.
            // Ее собственный контент отображается через специальную "обзорную" вкладку (__overview).
            if (!empty($item->getSubTabs()) && !str_ends_with($item->getId(), AbstractTab::OVERVIEW_POSTFIX)) {
                continue;
            }

            $isInitialActive = ($item->getId() === $activeTabId);
            $innerContent = $isInitialActive ? $this->render($item) : '';
            $restUrl = "/location/item/content/{$item->getId()}";

            $output .= sprintf(
                $paneWrapperHtml,
                $this->stringFilter->escAttr($item->getId()),
                $this->stringFilter->escAttr($item->getContentType()),
                $this->stringFilter->escUrl($restUrl),
                $isInitialActive ? 'is-loaded active' : '', // Добавляем классы, если контент уже загружен
                $innerContent
            );
        }

        return $output;
    }

    /**
     * Генерирует HTML-контент для указанной вкладки.
     *
     * @param ItemInterface $item
     *
     * @return string
     */
    public function render(ItemInterface $item): string
    {
        // Теперь, когда вкладка - это полноценный объект, она сама знает, как сгенерировать свой контент.
        // Вся логика с contentSource и DI-контейнером уже отработала при создании объекта $tab.
        return $item->getContent();
    }
}