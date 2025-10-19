<?php

namespace UserSpace\Common\Module\Grid\Src\Infrastructure;

use UserSpace\Common\Module\Grid\Src\Domain\TableContentGrid;

class UserListTableGrid extends TableContentGrid
{
    public function render(): string
    {
        $this->registerAssets();

        return parent::render();
    }

    private function registerAssets(): void
    {
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
            ['usp-core'],
            USERSPACE_VERSION,
            true
        );

        wp_localize_script('usp-table-grid-script', 'uspGridL10n', ['text' => [
            'loading' => __('Loading...', 'usp'),
            'error' => __('An error occurred. Please try again.', 'usp'),
        ]
        ]);
    }

    public function getEndpointPath(): string
    {
        return '/grid/users-table';
    }

    protected function getTableName(): string
    {
        return $this->queryBuilder->getWpdb()->users;
    }

    protected function getTableAlias(): string
    {
        return 'u';
    }

    protected function getSelectColumns(): array
    {
        return [
            'u.ID as id',
            'u.user_login as login',
            'u.user_email as email',
            'u.display_name as name',
            'u.user_registered as registered',
        ];
    }

    protected function getJoins(): array
    {
        // Для этого простого примера JOIN не нужны
        return [];
    }

    protected function getSearchableColumns(): array
    {
        return [
            'u.user_login',
            'u.user_email',
            'u.display_name',
        ];
    }

    protected function getColumnsConfig(): array
    {
        return [
            'id' => [
                'title' => __('ID', 'usp'),
                'sortable' => true,
            ],
            'login' => [
                'title' => __('Login', 'usp'),
                'sortable' => true,
            ],
            'name' => [
                'title' => __('Name', 'usp'),
                'sortable' => true,
            ],
            'email' => [
                'title' => __('Email', 'usp'),
                'sortable' => true,
            ],
            'registered' => [
                'title' => __('Registered', 'usp'),
                'sortable' => true,
            ],
        ];
    }
}