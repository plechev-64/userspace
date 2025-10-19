<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Common\Module\Grid\Src\Infrastructure\UserListTableGrid;

class UserTableListPage extends AbstractAdminPage
{
    public function __construct(private readonly UserListTableGrid $userListTableGrid)
    {
    }

    public function render(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->getPageTitle()) . '</h1>';
        echo '<p>' . esc_html__('A list of registered users with search, sort and pagination in a table view.', 'usp') . '</p>';

        echo $this->userListTableGrid->render();

        echo '</div>';
    }

    protected function getPageTitle(): string
    {
        return __('User List (Table)', 'usp');
    }

    protected function getMenuTitle(): string
    {
        return __('Users (Table)', 'usp');
    }

    protected function getMenuSlug(): string
    {
        return 'userspace-users-table';
    }

    protected function getParentSlug(): ?string
    {
        return 'userspace-settings';
    }
}