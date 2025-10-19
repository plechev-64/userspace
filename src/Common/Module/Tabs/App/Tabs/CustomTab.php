<?php

namespace UserSpace\Common\Module\Tabs\App\Tabs;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;

/**
 * Класс для кастомных вкладок, создаваемых пользователем в админ-панели.
 */
class CustomTab extends AbstractTab
{
    public function __construct()
    {
        // Свойства будут установлены из конфигурации в TabManager
    }

    public function getContent(): string
    {
        return '<p>' . sprintf(__('Content for the custom tab "%s" will be here.', 'usp'), $this->title) . '</p>';
    }
}