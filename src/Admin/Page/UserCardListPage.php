<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Common\Module\Grid\Src\Infrastructure\UserListGrid;
use UserSpace\Core\Admin\AdminApiInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;

class UserCardListPage extends AbstractAdminPage
{
    public function __construct(
        private readonly UserListGrid          $userListGrid,
        private readonly StringFilterInterface $str,
        AdminApiInterface                      $adminApi,
        HookManagerInterface                   $hookManager
    )
    {
        parent::__construct($adminApi, $hookManager);
    }

    public function getPageTitle(): string
    {
        return $this->str->translate('User List');
    }

    public function render(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . $this->str->escHtml($this->getPageTitle()) . '</h1>';
        echo '<p>' . $this->str->translate('A list of registered users with search and pagination.') . '</p>';

        echo $this->userListGrid->render();

        echo '</div>';
    }

    protected function getMenuTitle(): string
    {
        return $this->str->translate('Users (Cards)');
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