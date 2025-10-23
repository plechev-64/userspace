<?php

namespace UserSpace\Common\Module\Tabs\Src\Infrastructure;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;
use UserSpace\Common\Service\ViewedUserContext;
use UserSpace\Core\Profile\ProfileServiceApiInterface;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Сервис для рендеринга контента вкладки на основе ее contentSource.
 */
class TabRenderer
{
    public function __construct(
        private readonly TabManager                 $tabManager,
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
        $flatTabs = $this->tabManager->getAllRegisteredTabs(true);

        foreach ($flatTabs as $tab) {
            // Не рендерим контент-блок для "чистой" родительской вкладки,
            // так как ее роль - быть контейнером для подменю в навигации.
            // Ее собственный контент отображается через специальную "обзорную" вкладку (__overview).
            if (!empty($tab->getSubTabs()) && !str_ends_with($tab->getId(), '__overview')) {
                continue;
            }

            $isInitialActive = ($tab->getId() === $activeTabId);
            $innerContent = $isInitialActive ? $this->render($tab) : '';
            $restUrl = "/tabs/content/{$tab->getId()}";

            $output .= sprintf(
                $paneWrapperHtml,
                $this->stringFilter->escAttr($tab->getId()),
                $this->stringFilter->escAttr($tab->getContentType()),
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
     * @param AbstractTab $tab
     *
     * @return string
     */
    public function render(AbstractTab $tab): string
    {
        // Теперь, когда вкладка - это полноценный объект, она сама знает, как сгенерировать свой контент.
        // Вся логика с contentSource и DI-контейнером уже отработала при создании объекта $tab.
        return $tab->getContent();
    }
}