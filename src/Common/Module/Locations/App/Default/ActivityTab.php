<?php

namespace UserSpace\Common\Module\Locations\App\Default;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;

class ActivityTab extends AbstractTab
{
    public function __construct()
    {
        $this->id = 'activity';
        $this->title = __('Activity', 'usp');
        $this->order = 5;
        $this->location = 'header';
        $this->icon = 'dashicons-update';
    }

    public function getContent(): string
    {
        return '<p>' . __('User activity feed will be here.', 'usp') . '</p>';
    }
}