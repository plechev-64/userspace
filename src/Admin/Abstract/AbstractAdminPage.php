<?php

namespace UserSpace\Admin\Abstract;

/**
 * Абстрактный базовый класс для страниц в админ-панели WordPress.
 */
abstract class AbstractAdminPage
{
    protected string $hookSuffix = '';

    /**
     * Регистрирует страницу в меню WordPress.
     * Может быть как страницей верхнего уровня, так и подменю.
     */
    public function register(): void
    {
        $parentSlug = $this->getParentSlug();

        if (null === $parentSlug) {
            // Создаем страницу верхнего уровня
            $this->hookSuffix = add_menu_page(
                $this->getPageTitle(),
                $this->getMenuTitle(),
                $this->getCapability(),
                $this->getMenuSlug(),
                [$this, 'render'],
                $this->getIcon(),
                $this->getPosition()
            );
        } else {
            // Создаем подменю
            $this->hookSuffix = add_submenu_page(
                $parentSlug,
                $this->getPageTitle(),
                $this->getMenuTitle(),
                $this->getCapability(),
                $this->getMenuSlug(),
                [$this, 'render'],
                $this->getPosition()
            );
        }
    }

    /**
     * Рендерит содержимое страницы.
     * Дочерние классы должны реализовать этот метод для вывода своего HTML.
     */
    abstract public function render(): void;

    /**
     * Возвращает заголовок страницы (тег <title>).
     */
    abstract protected function getPageTitle(): string;

    /**
     * Возвращает название пункта в меню.
     */
    abstract protected function getMenuTitle(): string;

    /**
     * Возвращает slug страницы меню.
     */
    abstract protected function getMenuSlug(): string;

    /**
     * Возвращает slug родительского меню. Null для страницы верхнего уровня.
     */
    protected function getParentSlug(): ?string
    {
        return null;
    }

    protected function getCapability(): string
    {
        return 'manage_options';
    }

    protected function getIcon(): string
    {
        return '';
    }

    protected function getPosition(): ?int
    {
        return null;
    }
}