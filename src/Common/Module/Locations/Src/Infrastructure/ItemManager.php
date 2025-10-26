<?php

namespace UserSpace\Common\Module\Locations\Src\Infrastructure;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Common\Module\Locations\Src\Domain\ItemInterface;
use UserSpace\Common\Module\Locations\Src\Domain\ItemManagerInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Common\Service\ViewedUserContext;
use UserSpace\Core\Container\ContainerInterface;

class ItemManager implements ItemManagerInterface
{
    /** @var ItemInterface[] */
    private array $items = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ViewedUserContext  $viewedUserContext,
        private readonly UserApiInterface   $userApi
    )
    {
    }

    /**
     * Регистрирует элемент меню (вкладку или кнопку) по имени его класса.
     *
     * @param class-string<ItemInterface> $itemClassName Имя класса элемента.
     * @param array|null $configData Данные из конфигурации для обновления элемента.
     *
     * @throws \Exception
     */
    public function loadItem(string $itemClassName, ?array $configData = null): void
    {
        if (!class_exists($itemClassName) || !is_subclass_of($itemClassName, ItemInterface::class)) {
            // В реальном приложении здесь лучше логировать ошибку или бросать исключение
            return;
        }

        /** @var ItemInterface $item */
        $item = $this->container->get($itemClassName);

        // Если переданы данные из конфигурации, обновляем объект
        if (null !== $configData && method_exists($item, 'updateFromArray')) {
            $item->updateFromArray($configData);
        }

        // После всех инициализаций проверяем, что ID установлен.
        if (empty($item->getId())) {
            // Логируем ошибку или бросаем исключение, так как элемент без ID невалиден.
            return;
        }

        $this->items[$item->getId()] = $item;
    }

    /**
     * Возвращает элемент меню по его ID.
     */
    public function getItem(string $id): ?ItemInterface
    {
        // Если это ID "обзорной" вкладки (например, "profile__overview")
        if (str_ends_with($id, AbstractTab::OVERVIEW_POSTFIX)) {
            $parentId = str_replace(AbstractTab::OVERVIEW_POSTFIX, '', $id);
            $parentItem = $this->items[$parentId] ?? null;

            if ($parentItem instanceof AbstractTab) {
                // Создаем и возвращаем "обзорную" вкладку на лету
                return $this->createOverviewTab($parentItem);
            }

            return null;
        }

        // Обычный поиск по ID
        return $this->items[$id] ?? null;
    }

    /**
     * Возвращает отсортированный и отфильтрованный по правам доступа массив вкладок.
     *
     * @return ItemInterface[]
     */
    public function getItems(?string $location = null): array
    {
        $displayedUser = $this->viewedUserContext->getViewedUser();
        if (!$displayedUser) {
            return [];
        }
        $displayedUserId = $displayedUser->getId();
        $currentUserId = $this->userApi->getCurrentUserId();

        // Строим иерархию из всех зарегистрированных вкладок
        $hierarchicalItems = $this->buildItemsHierarchy(array_values($this->items));

        $allowedItems = [];
        foreach ($hierarchicalItems as $item) {
            // 1. Фильтрация по местоположению
            if (null !== $location && $item->getLocation() !== $location) {
                continue;
            }

            // 2. Проверка приватности: показываем, только если это владелец аккаунта
            if ($item->isPrivate() && (int)$currentUserId !== (int)$displayedUserId) {
                continue;
            }

            // 3. Проверка прав доступа для родительского элемента
            if ($item->canView()) {
                // Только вкладки могут иметь дочерние элементы
                if ($item instanceof AbstractTab) {
                    $allowedSubItems = [];
                    foreach ($item->getSubTabs() as $subItem) {
                        // Проверка приватности и прав для дочерних элементов
                        if ($subItem->isPrivate() && (int)$currentUserId !== (int)$displayedUserId) {
                            continue;
                        }
                        if ($subItem->canView()) {
                            $allowedSubItems[] = $subItem;
                        }
                    }
                    // Сортируем дочерние элементы по полю order
                    usort($allowedSubItems, static fn(ItemInterface $a, ItemInterface $b): int => $a->getOrder() <=> $b->getOrder());

                    // Перезаписываем subItems только отфильтрованными и отсортированными
                    // Для этого нужно создать клон, чтобы не изменять исходный объект в $this->items
                    $clonedItem = clone $item;
                    $clonedItem->setSubTabs($allowedSubItems);
                    $allowedItems[] = $clonedItem;
                } else {
                    // Если это кнопка (или вкладка без дочерних элементов), просто добавляем ее
                    $allowedItems[] = $item;
                }
            }
        }

        // 4. Сортируем основные элементы по полю order
        usort($allowedItems, static fn(ItemInterface $a, ItemInterface $b): int => $a->getOrder() <=> $b->getOrder());

        return $allowedItems;
    }

    /**
     * Возвращает все зарегистрированные элементы в виде иерархии без фильтрации прав.
     *
     * @param bool $flat Вернуть плоский список вместо иерархии.
     * @return ItemInterface[]
     */
    public function getAllRegisteredItems(bool $flat = false): array
    {
        $hierarchicalItems = $this->buildItemsHierarchy(array_values($this->items));

        if ($flat) {
            $flatList = [];
            // Рекурсивная функция для преобразования иерархии в плоский список.
            $flattener = static function (array $items) use (&$flatList, &$flattener): void {
                foreach ($items as $item) {
                    $flatList[] = $item;
                    if ($item instanceof AbstractTab && !empty($item->getSubTabs())) {
                        $flattener($item->getSubTabs()); // Рекурсия только для вкладок
                    }
                }
            };
            $flattener($hierarchicalItems);

            return $flatList;
        }

        usort($hierarchicalItems, static fn(ItemInterface $a, ItemInterface $b): int => $a->getOrder() <=> $b->getOrder());

        return $hierarchicalItems;
    }

    /**
     * Строит иерархическую структуру из плоского списка элементов.
     *
     * @param ItemInterface[] $items Плоский массив объектов ItemInterface.
     *
     * @return ItemInterface[] Массив ItemInterface верхнего уровня с заполненным свойством subTabs.
     */
    private function buildItemsHierarchy(array $items): array
    {
        /** @var array<string, ItemInterface> $itemsById */
        $itemsById = [];
        // Используем array_map для создания клонов, чтобы гарантировать работу с "чистыми" объектами
        // и не изменять состояние оригинальных объектов в $this->items.
        $clonedItems = array_map(fn($item) => clone $item, $items);

        foreach ($clonedItems as $item) {
            if ($item instanceof AbstractTab) {
                $item->setSubTabs([]); // Сбрасываем subTabs на случай повторного построения
            }
            $itemsById[$item->getId()] = $item;
        }

        foreach ($itemsById as $item) {
            $parentId = $item->getParentId();
            if ($parentId && isset($itemsById[$parentId])) {
                if ($itemsById[$parentId] instanceof AbstractTab) {
                    $itemsById[$parentId]->addSubTab($item);
                }
            }
        }

        // Создаем "обзорные" подвкладки для родителей, у которых есть контент и другие дочерние вкладки
        foreach ($itemsById as $item) {
            // "Обзорные" вкладки создаются только для реальных вкладок (не для кнопок)
            if ($item instanceof AbstractTab && count($item->getSubTabs()) > 0) {
                $overviewTab = $this->createOverviewTab($item);

                // Помещаем "обзорную" вкладку в начало списка дочерних
                $subItems = $item->getSubTabs();
                array_unshift($subItems, $overviewTab);
                $item->setSubTabs($subItems);
            }
        }

        return array_filter($itemsById, fn(ItemInterface $item) => $item->getParentId() === null);
    }

    /**
     * Создает и настраивает "обзорную" вкладку на основе родительской.
     */
    private function createOverviewTab(AbstractTab $parentTab): AbstractTab
    {
        // Создаем ГАРАНТИРОВАННО НОВЫЙ экземпляр того же класса, что и родительская вкладка.
        // Метод `build` создает новый объект, разрешая зависимости конструктора, в отличие от `get`, который может вернуть синглтон.
        /** @var AbstractTab $overviewTab */
        $overviewTab = $this->container->build(get_class($parentTab));

        // Копируем данные из родительской вкладки в новый объект.
        // Это безопаснее, чем clone, так как мы явно контролируем, что копируется,
        // и избегаем проблем с поверхностным копированием.
        $overviewTab->updateFromArray($parentTab->toArray());

        // Устанавливаем уникальные свойства для "обзорной" вкладки.
        $overviewTab->setId($parentTab->getId() . AbstractTab::OVERVIEW_POSTFIX); // Новый ID
        $overviewTab->setParentId($parentTab->getId());       // Устанавливаем родителя
        $overviewTab->setSubTabs([]);                         // У обзорной вкладки не может быть дочерних

        return $overviewTab;
    }
}