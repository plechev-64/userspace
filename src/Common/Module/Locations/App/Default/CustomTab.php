<?php

namespace UserSpace\Common\Module\Locations\App\Default;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TemplateManagerInterface;

/**
 * Класс для кастомных вкладок, создаваемых пользователем в админ-панели.
 */
class CustomTab extends AbstractTab
{
    public function __construct(
        private readonly StringFilterInterface $str,
        TemplateManagerInterface $templateManager
    )
    {
        parent::__construct($templateManager);
        // Свойства будут установлены из конфигурации в TabManager
    }

    public function getContent(): string
    {
        return '<p>' . sprintf($this->str->escHtml('Content for the custom tab "%s" will be here.'), $this->title) . '</p>';
    }
}