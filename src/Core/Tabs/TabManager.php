<?php

namespace UserSpace\Core\Tabs;

use UserSpace\Core\ViewedUserContext;

class TabManager
{
	/** @var TabDto[] */
	private array $tabs = [];

	public function __construct(private readonly ViewedUserContext $viewedUserContext)
	{
	}

    /**
     * Регистрирует или обновляет вкладку.
     * @param TabDto $tabDto
     */
    public function registerTab(TabDto $tabDto): void
    {
        $this->tabs[$tabDto->id] = $tabDto;
    }

    /**
     * Возвращает отсортированный и отфильтрованный по правам доступа массив вкладок.
     * @param string|null $location Местоположение для фильтрации вкладок. Если null, возвращает все.
     * @return TabDto[]
     */
    public function getTabs(?string $location = null): array
    {
		$displayedUser = $this->viewedUserContext->getViewedUser();
		if ( ! $displayedUser) {
			return [];
		}
		$displayedUserId = $displayedUser->ID;

		$allowedTabs   = [];
		$currentUserId = get_current_user_id();

        $hierarchicalTabs = $this->buildTabsHierarchy(array_values($this->tabs)); // phpcs:ignore

        foreach ($hierarchicalTabs as $tab) {
            // Фильтрация по местоположению
            if (null !== $location && $tab->location !== $location) {
                continue;
            }

            // Проверка приватности: показываем, только если это владелец аккаунта
            if ($tab->isPrivate && (int) $currentUserId !== (int) $displayedUserId) {
                continue;
            }

            // Проверка прав доступа
            if (current_user_can($tab->capability, $displayedUserId)) {
                $allowedSubTabs = [];
                foreach ($tab->subTabs as $subTab) {
                    if ($subTab->isPrivate && (int) $currentUserId !== (int) $displayedUserId) {
                        continue;
                    }
                    if (current_user_can($subTab->capability, $displayedUserId)) {
                        $allowedSubTabs[] = $subTab;
                    }
                }
                // Сортируем подвкладки
                usort($allowedSubTabs, static fn(TabDto $a, TabDto $b): int => $a->order <=> $b->order);
                $tab->subTabs = $allowedSubTabs;
                $allowedTabs[] = $tab;
            }
        }

        // Сортируем основные вкладки
        usort($allowedTabs, static fn(TabDto $a, TabDto $b): int => $a->order <=> $b->order);

        return $allowedTabs;
    }

    /**
     * Возвращает все зарегистрированные вкладки без фильтрации.
     * @return TabDto[]
     */
    public function getAllRegisteredTabs(): array
    {
        return $this->buildTabsHierarchy(array_values($this->tabs));
    }

    /**
     * Строит иерархическую структуру из плоского списка вкладок.
     *
     * @param TabDto[] $tabs Плоский массив объектов TabDto.
     *
     * @return TabDto[] Массив TabDto верхнего уровня с заполненным свойством subTabs.
     */
    public function buildTabsHierarchy(array $tabs): array
    {
        $tabsById = [];
        foreach ($tabs as $tab) {
            $tabsById[$tab->id] = $tab;
            $tab->subTabs = []; // Сбрасываем subTabs на случай повторного построения
        }

        foreach ($tabsById as $tab) {
            if ($tab->parentId && isset($tabsById[$tab->parentId])) {
                $tabsById[$tab->parentId]->subTabs[] = $tab;
            }
        }

        return array_filter($tabsById, fn(TabDto $tab) => $tab->parentId === null);
    }
}