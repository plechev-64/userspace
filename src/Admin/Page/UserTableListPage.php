<?php

namespace UserSpace\Admin\Page;

use UserSpace\Core\Helper\StringFilterInterface;
use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Common\Module\Grid\Src\Infrastructure\UserListTableGrid;

class UserTableListPage extends AbstractAdminPage
{
    public function __construct(
        private readonly UserListTableGrid     $userListTableGrid,
        private readonly StringFilterInterface $str
    )
    {
    }

    public function render(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . $this->str->escHtml($this->getPageTitle()) . '</h1>';
        echo '<p>' . $this->str->translate('A list of registered users with search, sort and pagination in a table view.') . '</p>';

        echo $this->userListTableGrid->render();

        echo '</div>';
    }

    public function getPageTitle(): string
    {
        return $this->str->translate('User List (Table)');
    }

    protected function getMenuTitle(): string
    {
        return $this->str->translate('Users (Table)');
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