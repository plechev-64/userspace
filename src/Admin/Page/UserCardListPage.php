<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Core\Grid\UserListGrid;

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
            'usp-card-grid-style',
            USERSPACE_PLUGIN_URL . 'assets/css/card-grid.css',
            ['usp-base-grid-style'],
            USERSPACE_VERSION
        );

        wp_enqueue_script(
            'usp-card-grid-script',
            USERSPACE_PLUGIN_URL . 'assets/js/card-grid.js',
            ['usp-core'],
            USERSPACE_VERSION,
            true
        );

        wp_localize_script('usp-card-grid-script', 'uspGridL10n', [
            'text' => [
                'loading' => __('Loading...', 'usp'),
                'error'   => __('An error occurred. Please try again.', 'usp'),
            ],
        ]);
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