<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Module\Grid\Src\Infrastructure\UserListGrid;

class UserCardListPage extends AbstractAdminPage
{
    public function __construct(private readonly UserListGrid $userListGrid)
    {
    }

    protected function getPageTitle(): string
    {
        return __('User List', 'usp');
    }
    
    public function render(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->getPageTitle()) . '</h1>';
        echo '<p>' . __('A list of registered users with search and pagination.', 'usp') . '</p>';

        echo $this->userListGrid->render();

        echo '</div>';
    }

    protected function getMenuTitle(): string
    {
        return __('Users (Cards)', 'usp');
    }

    protected function getMenuSlug(): string
    {
        return 'userspace-users-cards';
    }

    protected function getParentSlug(): ?string
    {
        return 'userspace-settings';
    }
}