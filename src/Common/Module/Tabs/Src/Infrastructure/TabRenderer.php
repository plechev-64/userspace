<?php

namespace UserSpace\Common\Module\Tabs\Src\Infrastructure;
use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;
use UserSpace\Common\Service\ViewedUserContext;

/**
 * Сервис для рендеринга контента вкладки на основе ее contentSource.
 */
class TabRenderer
{
	public function __construct(
		private readonly TabManager $tabManager,
        private readonly ViewedUserContext $viewedUserContext
	) {
	}

    /**
     * Рендерит HTML для всех панелей контента вкладок.
     *
     * @param string      $paneWrapperHtml HTML-шаблон для обертки одной панели, использующий плейсхолдеры sprintf.
     * @param string|null $mainLocation    Идентификатор локации, чья первая вкладка должна быть активна по умолчанию.
     *
     * @return string
     * @throws \Exception
     */
	public function renderTabsContent(string $paneWrapperHtml, ?string $mainLocation = null): string
	{
        $output = '';
        $viewedUser = $this->viewedUserContext->getViewedUser();
        if ( ! $viewedUser) {
            return '';
        }

        // Определяем ID изначально активной вкладки
        $firstActiveTabId = null;
        if ($mainLocation) {
            $mainLocationTabs = $this->tabManager->getTabs($mainLocation);

            if ( ! empty($mainLocationTabs)) {
                $firstParent = $mainLocationTabs[0];
                $subTabs = $firstParent->getSubTabs();
                $firstActiveTabId = ! empty($subTabs) ? $subTabs[0]->getId() : $firstParent->getId();
            }
        }

        // Получаем плоский список всех зарегистрированных вкладок
		$flatTabs = $this->tabManager->getAllRegisteredTabs(true);

		foreach ($flatTabs as $tab) {
			// Не рендерим контент-блок для "чистой" родительской вкладки,
			// так как ее роль - быть контейнером для подменю в навигации.
			// Ее собственный контент отображается через специальную "обзорную" вкладку (__overview).
			if ( ! empty($tab->getSubTabs()) && ! str_ends_with($tab->getId(), '__overview')) {
				continue;
			}

			$isInitialActive = ($tab->getId() === $firstActiveTabId);
			$innerContent = $isInitialActive ? $this->render($tab) : '';
			$restUrl = "/tab-content/{$tab->getId()}";

			$output .= sprintf(
				$paneWrapperHtml,
				esc_attr($tab->getId()),
				esc_attr($tab->getContentType()),
				esc_url($restUrl),
				$isInitialActive ? 'is-loaded active' : '', // Добавляем классы, если контент уже загружен
				$innerContent
			);
		}

		return $output;
	}

    /**
     * Рендерит меню вкладок для указанной локации.
     *
     * @param string $location       Идентификатор локации ('header', 'sidebar', etc.).
     * @param bool   $activate_first Сделать ли первую вкладку в меню активной.
     *
     * @return string Сгенерированный HTML-код меню.
     */
    public function renderMenu(string $location, bool $activate_first = false): string
    {
        $tabs_to_render = $this->tabManager->getTabs($location);

        if (empty($tabs_to_render)) {
            return '';
        }

        // Используем буферизацию вывода для захвата HTML из файла шаблона.
        ob_start();
        // Передаем переменные в область видимости файла.
        include USERSPACE_PLUGIN_DIR . 'themes/first/parts/tab-menu.php';

        return ob_get_clean();
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