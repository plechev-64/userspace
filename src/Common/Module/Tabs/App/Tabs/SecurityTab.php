<?php

namespace UserSpace\Common\Module\Tabs\App\Tabs;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;

class SecurityTab extends AbstractTab
{
    public function __construct()
    {
        $this->id = 'security';
        $this->title = __('Security', 'usp');
        $this->order = 20;
        $this->location = 'sidebar';
        $this->icon = 'dashicons-shield';
    }

    public function getContent(): string
    {
        return '<p>' . __('Security settings will be here.', 'usp') . '</p>';
    }
}