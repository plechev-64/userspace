<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Core\Grid\UserListTableGrid;

class UserTableListPage extends AbstractAdminPage
{
    public function __construct(private readonly UserListTableGrid $userListTableGrid)
    {
    }

    public function enqueueAssets(string $hook): void
    {
        if ($this->hookSuffix !== $hook) {
            return;
        }

        wp_enqueue_style(
            'usp-base-grid-style',
            USERSPACE_PLUGIN_URL . 'assets/css/base-grid.css',
            [],
            USERSPACE_VERSION
        );
        wp_enqueue_style(
            'usp-table-grid-style',
            USERSPACE_PLUGIN_URL . 'assets/css/table-grid.css',
            ['usp-base-grid-style'],
            USERSPACE_VERSION
        );

        wp_enqueue_script(
            'usp-table-grid-script',
            USERSPACE_PLUGIN_URL . 'assets/js/table-grid.js',
            [],
            USERSPACE_VERSION,
            true
        );

        wp_localize_script('usp-table-grid-script', 'uspGridL10n', [ 'text' => [
                'loading' => __('Loading...', 'usp'),
                'error' => __('An error occurred. Please try again.', 'usp'),
            ]
        ]);
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