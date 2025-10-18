<?php

namespace UserSpace\Core\Tabs;

use UserSpace\Core\ContainerInterface;
use UserSpace\Core\ViewedUserContext;

class TabManager
{
    /** @var AbstractTab[] */
    private array $tabs = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ViewedUserContext $viewedUserContext
    ) {
    }

    /**
     * Регистрирует вкладку по имени её класса.
     *
     * @param class-string<AbstractTab> $tabClassName Имя класса вкладки.
     * @param array|null                $configData   Данные из конфигурации для обновления вкладки.
     *
     */
    public function registerTab(string $tabClassName, ?array $configData = null): void
    {
        if (!class_exists($tabClassName) || !is_subclass_of($tabClassName, AbstractTab::class)) {
            // В реальном приложении здесь лучше логировать ошибку или бросать исключение
            return;
        }

        /** @var AbstractTab $tab */
        $tab = $this->container->get($tabClassName);

        // Если переданы данные из конфигурации, обновляем объект вкладки
        if (null !== $configData) {
            $tab->updateFromArray($configData);
        }

        // После всех инициализаций проверяем, что ID установлен.
        if (empty($tab->getId())) {
            // Логируем ошибку или бросаем исключение, так как вкладка без ID невалидна.
            return;
        }

        $this->tabs[$tab->getId()] = $tab;
    }

    /**
     * Возвращает вкладку по её ID.
     */
    public function getTab(string $id): ?AbstractTab
    {
        // Если это ID "обзорной" вкладки (например, "profile__overview")
        if (str_ends_with($id, '_overview')) {
            $parentId = str_replace('_overview', '', $id);
            $parentTab = $this->tabs[$parentId] ?? null;

            if ($parentTab) {
                // Создаем и возвращаем "обзорную" вкладку на лету
                return $this->createOverviewTab($parentTab);
            }

            return null;
        }

        // Обычный поиск по ID
        return $this->tabs[$id] ?? null;
    }

    /**
     * Возвращает отсортированный и отфильтрованный по правам доступа массив вкладок.
     *
     * @return AbstractTab[]
     */
    public function getTabs(?string $location = null): array
    {
        $displayedUser = $this->viewedUserContext->getViewedUser();
        if (!$displayedUser) {
            return [];
        }
        $displayedUserId = $displayedUser->ID;
        $currentUserId = get_current_user_id();

        // Строим иерархию из всех зарегистрированных вкладок
        $hierarchicalTabs = $this->buildTabsHierarchy(array_values($this->tabs));

        $allowedTabs = [];
        foreach ($hierarchicalTabs as $tab) {
            // 1. Фильтрация по местоположению
            if (null !== $location && $tab->getLocation() !== $location) {
                continue;
            }

            // 2. Проверка приватности: показываем, только если это владелец аккаунта
            if ($tab->isPrivate() && (int) $currentUserId !== (int) $displayedUserId) {
                continue;
            }

            // 3. Проверка прав доступа для родительской вкладки
            if (current_user_can($tab->getCapability(), $displayedUserId)) {
                $allowedSubTabs = [];
                foreach ($tab->getSubTabs() as $subTab) {
                    // Проверка приватности и прав для дочерних вкладок
                    if ($subTab->isPrivate() && (int) $currentUserId !== (int) $displayedUserId) {
                        continue;
                    }
                    if (current_user_can($subTab->getCapability(), $displayedUserId)) {
                        $allowedSubTabs[] = $subTab;
                    }
                }
                // Сортируем подвкладки по полю order
                usort($allowedSubTabs, static fn (AbstractTab $a, AbstractTab $b): int => $a->getOrder() <=> $b->getOrder());

                // Перезаписываем subTabs только отфильтрованными и отсортированными
                // Для этого нужно создать клон, чтобы не изменять исходный объект в $this->tabs
                $clonedTab = clone $tab;
                $clonedTab->setSubTabs($allowedSubTabs);
                $allowedTabs[] = $clonedTab;
            }
        }

        // 4. Сортируем основные вкладки по полю order
        usort($allowedTabs, static fn (AbstractTab $a, AbstractTab $b): int => $a->getOrder() <=> $b->getOrder());

        return $allowedTabs;
    }

    /**
     * Возвращает все зарегистрированные вкладки в виде иерархии без фильтрации прав.
     *
     * @param bool $flat Вернуть плоский список вместо иерархии.
     * @return AbstractTab[]
     */
    public function getAllRegisteredTabs(bool $flat = false): array
    {
        $hierarchicalTabs = $this->buildTabsHierarchy(array_values($this->tabs));

        if ($flat) {
            $flatList = [];
            // Рекурсивная функция для преобразования иерархии в плоский список.
            $flattener = static function (array $tabs) use (&$flatList, &$flattener): void {
                foreach ($tabs as $tab) {
                    $flatList[] = $tab;
                    if ( ! empty($tab->getSubTabs())) {
                        $flattener($tab->getSubTabs());
                    }
                }
            };
            $flattener($hierarchicalTabs);

            return $flatList;
        }

        usort($hierarchicalTabs, static fn (AbstractTab $a, AbstractTab $b): int => $a->getOrder() <=> $b->getOrder());

        return $hierarchicalTabs;
    }

    /**
     * Строит иерархическую структуру из плоского списка вкладок.
     *
     * @param AbstractTab[] $tabs Плоский массив объектов AbstractTab.
     *
     * @return AbstractTab[] Массив AbstractTab верхнего уровня с заполненным свойством subTabs.
     */
    private function buildTabsHierarchy(array $tabs): array
    {
        /** @var array<string, AbstractTab> $tabsById */
        $tabsById = [];
        foreach ($tabs as $tab) {
            // Клонируем, чтобы не изменять оригинальные объекты в $this->tabs
            $clonedTab = clone $tab;
            $clonedTab->setSubTabs([]); // Сбрасываем subTabs на случай повторного построения
            $tabsById[$clonedTab->getId()] = $clonedTab;
        }

        foreach ($tabsById as $tab) {
            $parentId = $tab->getParentId();
            if ($parentId && isset($tabsById[$parentId])) {
                $tabsById[$parentId]->addSubTab($tab);
            }
        }

        // Создаем "обзорные" подвкладки для родителей, у которых есть контент и другие дочерние вкладки
        foreach ($tabsById as $tab) {
            if (count($tab->getSubTabs()) > 0 && !empty(trim($tab->getContent()))) {
                $overviewTab = $this->createOverviewTab($tab);

                // Помещаем "обзорную" вкладку в начало списка дочерних
                $subTabs = $tab->getSubTabs();
                array_unshift($subTabs, $overviewTab);
                $tab->setSubTabs($subTabs);
            }
        }

        return array_filter($tabsById, fn (AbstractTab $tab) => $tab->getParentId() === null);
    }

    /**
     * Создает и настраивает "обзорную" вкладку на основе родительской.
     */
    private function createOverviewTab(AbstractTab $parentTab): AbstractTab
    {
        $overviewTab = clone $parentTab;
        $overviewTab->setSubTabs([]);
        $overviewTab->setParentId($parentTab->getId());
        $overviewTab->setId($parentTab->getId() . '_overview');

        return $overviewTab;
    }
}