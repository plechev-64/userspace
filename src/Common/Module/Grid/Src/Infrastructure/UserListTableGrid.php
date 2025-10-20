<?php

namespace UserSpace\Common\Module\Grid\Src\Infrastructure;

use UserSpace\Common\Module\Grid\Src\Domain\TableContentGrid;
use UserSpace\Core\AssetRegistryInterface;
use UserSpace\Core\Database\DatabaseConnectionInterface;
use UserSpace\Core\StringFilterInterface;

class UserListTableGrid extends TableContentGrid
{
    public function __construct(
        DatabaseConnectionInterface $db,
        StringFilterInterface       $str,
        AssetRegistryInterface      $assetRegistry
    ) {
        parent::__construct($db, $str, $assetRegistry);
    }

    public function render(): string
    {
        $this->registerAssets();

        return parent::render();
    }

    public function getEndpointPath(): string
    {
        return '/grid/users-table';
    }

    protected function getTableName(): string
    {
        return $this->db->getUsersTableName();
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
                'title' => $this->str->translate('ID'),
                'sortable' => true,
            ],
            'login' => [
                'title' => $this->str->translate('Login'),
                'sortable' => true,
            ],
            'name' => [
                'title' => $this->str->translate('Name'),
                'sortable' => true,
            ],
            'email' => [
                'title' => $this->str->translate('Email'),
                'sortable' => true,
            ],
            'registered' => [
                'title' => $this->str->translate('Registered'),
                'sortable' => true,
            ],
        ];
    }

    private function registerAssets(): void
    {
        $this->assetRegistry->enqueueStyle(
            'usp-base-grid-style',
            USERSPACE_PLUGIN_URL . 'assets/css/base-grid.css',
            [],
            USERSPACE_VERSION
        );
        $this->assetRegistry->enqueueStyle(
            'usp-table-grid-style',
            USERSPACE_PLUGIN_URL . 'assets/css/table-grid.css',
            ['usp-base-grid-style'],
            USERSPACE_VERSION
        );

        $this->assetRegistry->enqueueScript(
            'usp-table-grid-script',
            USERSPACE_PLUGIN_URL . 'assets/js/table-grid.js',
            ['usp-core'],
            USERSPACE_VERSION,
            true
        );

        $this->assetRegistry->localizeScript('usp-table-grid-script', 'uspGridL10n', ['text' => [
            'loading' => $this->str->translate('Loading...'),
            'error' => $this->str->translate('An error occurred. Please try again.'),
        ]
        ]);
    }
}