<?php

namespace UserSpace\Core\Grid;

class UserListTableGrid extends TableContentGrid
{
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

    public function getEndpointPath(): string
    {
        return '/grid/users-table';
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

    /**
     * Переопределяем, чтобы добавить форматирование даты
     */
    public function renderItems(array $items): string
    {
        foreach ($items as $item) {
            $item->registered = mysql2date(get_option('date_format'), $item->registered);
        }
        return parent::renderItems($items);
    }
}