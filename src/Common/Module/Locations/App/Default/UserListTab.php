<?php

namespace UserSpace\Common\Module\Locations\App\Default;

use UserSpace\Common\Module\Grid\Src\Infrastructure\UserListGrid;
use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;

class UserListTab extends AbstractTab
{
    public function __construct(private readonly UserListGrid $userListGrid)
    {
        $this->id = 'user_list';
        $this->title = __('Users', 'usp');
        $this->location = 'sidebar';
        $this->order = 30;
        $this->icon = 'dashicons-groups';
        $this->contentType = 'rest';
    }

    /**
     * @throws \Exception
     */
    public function getContent(): string
    {
        return $this->userListGrid->render();
    }
}