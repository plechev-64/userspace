<?php

namespace UserSpace\Core\Tabs;

/**
 * Класс для генерации контента системных вкладок.
 */
class TabContentGenerator
{
    /**
     * Рендерит контент для вкладки "Безопасность".
     */
    public function renderSecurityContent(TabDto $tab): string
    {
        return '<p>' . __('Security settings will be here.', 'usp') . '</p>';
    }

    /**
     * Рендерит контент для вкладки "Активность".
     */
    public function renderActivityContent(TabDto $tab): string
    {
        return '<p>' . __('User activity feed will be here.', 'usp') . '</p>';
    }
}