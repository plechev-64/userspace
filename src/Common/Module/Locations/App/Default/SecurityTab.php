<?php

namespace UserSpace\Common\Module\Locations\App\Default;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TemplateManagerInterface;

class SecurityTab extends AbstractTab
{
    public function __construct(
        private readonly StringFilterInterface $str,
        TemplateManagerInterface $templateManager
    )
    {
        parent::__construct($templateManager);
        $this->id = 'security';
        $this->title = $this->str->translate('Security');
        $this->order = 20;
        $this->location = 'sidebar';
        $this->icon = 'dashicons-shield';
    }

    public function getContent(): string
    {
        return '<p>' . $this->str->translate('Security settings will be here.') . '</p>';
    }
}