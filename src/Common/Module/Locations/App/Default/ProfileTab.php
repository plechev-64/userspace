<?php

namespace UserSpace\Common\Module\Locations\App\Default;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;

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