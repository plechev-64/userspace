<?php

namespace UserSpace\Core\Tabs;

use UserSpace\Core\ContainerInterface;
use UserSpace\Core\ViewedUserContext;

/**
 * Сервис для рендеринга контента вкладки на основе ее contentSource.
 */
class TabRenderer
{
	public function __construct(
		private readonly ContainerInterface $container,
		private readonly TabManager $tabManager,
        private readonly ViewedUserContext $viewedUserContext
	) {
	}

    /**
     * Рендерит HTML для всех панелей контента вкладок.
     *
     * @param string $paneWrapperHtml HTML-шаблон для обертки одной панели, использующий плейсхолдеры sprintf.
     *
     * @return string
     * @throws \Exception
     */
	public function renderTabsContent(string $paneWrapperHtml): string
	{
        $output = '';
        $viewedUser = $this->viewedUserContext->getViewedUser();
        if ( ! $viewedUser) {
            return '';
        }

        // Определяем ID изначально активной вкладки
        $firstActiveTabId = null;
        $sidebarTabs = $this->tabManager->getTabs('sidebar');
        if ( ! empty($sidebarTabs)) {
            $firstParent = $sidebarTabs[0];
            $firstActiveTabId = ! empty($firstParent->subTabs) ? $firstParent->subTabs[0]->id : $firstParent->id;
		}

        $allTabs = $this->tabManager->getAllRegisteredTabs();
		$flatTabs = array_merge($allTabs, ...array_column($allTabs, 'subTabs'));

		foreach ($flatTabs as $tab) {
			$isInitialActive = ($tab->id === $firstActiveTabId);
			$innerContent = $isInitialActive ? $this->render($tab) : '';
			$restUrl = "/tab-content/{$tab->id}";

			$output .= sprintf(
				$paneWrapperHtml,
				esc_attr($tab->id),
				esc_attr($tab->contentType),
				esc_url($restUrl),
				$isInitialActive ? 'is-loaded' : '', // Добавляем класс, если контент уже загружен
				$innerContent
			);
		}

		return $output;
	}

    /**
     * Генерирует HTML-контент для указанной вкладки.
     *
     * @param TabDto $tab
     *
     * @return string
     * @throws \Exception
     */
    public function render(TabDto $tab): string
    {
        $source = $tab->contentSource ?? null;

        if (empty($source)) {
            return '';
        }

        // Если это массив [className, methodName]
        if (is_array($source) && isset($source[0], $source[1]) && is_string($source[0])) {
            if ($this->container->has($source[0])) {
                $controller = $this->container->get($source[0]);
                $method = $source[1];

                if (is_callable([$controller, $method])) {
                    return call_user_func([$controller, $method], $tab);
                }
            }
        }

        // Если это обычный callable (для обратной совместимости или простых случаев)
        if (is_callable($source)) {
            return call_user_func($source, $tab);
        }

        return '';
    }
}