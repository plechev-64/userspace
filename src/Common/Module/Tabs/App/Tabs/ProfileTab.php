<?php

namespace UserSpace\Common\Module\Tabs\App\Tabs;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;

class ProfileTab extends AbstractTab
{
    public function __construct()
    {
        $this->id = 'profile';
        $this->title = __('Profile', 'usp');
        $this->order = 10;
        $this->location = 'sidebar';
        $this->icon = 'dashicons-admin-users';
    }

    public function getContent(): string
    {
        return 'Это содержимое родительской вкладки "Профиль". Обычно здесь отображается какая-то общая информация.';
    }
}