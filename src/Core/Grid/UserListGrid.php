<?php

namespace UserSpace\Core\Grid;

use UserSpace\Core\Database\QueryBuilder;

class UserListGrid extends AbstractListContentGrid
{
    public function __construct(QueryBuilder $queryBuilder)
    {
        parent::__construct($queryBuilder);
    }

    public function getEndpointPath(): string
    {
        return '/grid/users';
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
            'u.display_name as display_name',
            'um_fn.meta_value as first_name',
            'um_ln.meta_value as last_name',
        ];
    }

    protected function getJoins(): array
    {
        $usermetaTable = $this->queryBuilder->getWpdb()->usermeta;
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
        return USERSPACE_PLUGIN_DIR . 'views/grid/user-item.php';
    }
}