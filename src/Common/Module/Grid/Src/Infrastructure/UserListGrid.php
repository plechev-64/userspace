<?php

namespace UserSpace\Common\Module\Grid\Src\Infrastructure;

use UserSpace\Common\Module\Grid\Src\Domain\AbstractListContentGrid;
use UserSpace\Common\Service\TemplateManager;
use UserSpace\Core\Database\DatabaseConnectionInterface;
use UserSpace\Core\StringFilterInterface;

class UserListGrid extends AbstractListContentGrid
{
    public function __construct(
        DatabaseConnectionInterface      $db,
        private readonly TemplateManager $templateManager,
        StringFilterInterface            $str
    )
    {
        parent::__construct($db, $str);
    }

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
                'error' => __('An error occurred. Please try again.', 'usp'),
            ],
        ]);
    }

    public function getEndpointPath(): string
    {
        return '/grid/users';
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
            'u.display_name as display_name',
            'um_fn.meta_value as first_name',
            'um_ln.meta_value as last_name',
        ];
    }

    protected function getJoins(): array
    {
        $usermetaTable = $this->db->getUsermetaTableName();
        return [
            [
                'type' => 'LEFT JOIN',
                'table' => $usermetaTable,
                'alias' => 'um_fn',
                'on' => "u.ID = um_fn.user_id AND um_fn.meta_key = 'first_name'",
            ],
            [
                'type' => 'LEFT JOIN',
                'table' => $usermetaTable,
                'alias' => 'um_ln',
                'on' => "u.ID = um_ln.user_id AND um_ln.meta_key = 'last_name'",
            ],
        ];
    }

    protected function getSearchableColumns(): array
    {
        return [
            'u.user_login',
            'u.user_email',
            'u.display_name',
            'um_fn.meta_value',
            'um_ln.meta_value',
        ];
    }

    protected function getItemTemplatePath(): string
    {
        return $this->templateManager->getTemplatePath('grid_user_item');
    }
}