<?php

namespace UserSpace\Common\Module\Locations\App\Default;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TemplateManagerInterface;

class ActivityTab extends AbstractTab
{
    public function __construct(
        private readonly StringFilterInterface $str,
        TemplateManagerInterface $templateManager
    )
    {
        parent::__construct($templateManager);
        $this->id = 'activity';
        $this->title = $str->translate('Activity', 'usp');
        $this->order = 5;
        $this->location = 'header';
        $this->icon = 'dashicons-update';
    }

    public function getContent(): string
    {
        return '<p>' . $this->str->translate('User activity feed will be here.', 'usp') . '</p>';
    }
}