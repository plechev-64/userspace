<?php

namespace UserSpace\Module\Grid\Src\Infrastructure;

use UserSpace\Module\Grid\Src\Domain\TableContentGrid;

class QueueJobsGrid extends TableContentGrid
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

        wp_localize_script('usp-table-grid-script', 'uspGridL10n', [ 'text' => [
                'loading' => __('Loading...', 'usp'),
                'error' => __('An error occurred. Please try again.', 'usp'),
            ]
        ]);
    }

    public function getEndpointPath(): string
    {
        return '/grid/queue-jobs';
    }

    protected function getTableName(): string
    {
        return $this->queryBuilder->getWpdb()->prefix . 'userspace_jobs';
    }

    protected function getTableAlias(): string
    {
        return 'j';
    }

    protected function getSelectColumns(): array
    {
        return [
            'j.id',
            'j.message_class',
            'j.status',
            'j.attempts',
            'j.available_at',
            'j.created_at',
        ];
    }

    protected function getJoins(): array
    {
        return [];
    }

    protected function getSearchableColumns(): array
    {
        return [
            'j.message_class',
            'j.status',
        ];
    }

    protected function getColumnsConfig(): array
    {
        return [
            'id' => [
                'title' => __('ID', 'usp'),
                'sortable' => true,
            ],
            'message_class' => [
                'title' => __('Message Class', 'usp'),
                'sortable' => true,
            ],
            'status' => [
                'title' => __('Status', 'usp'),
                'sortable' => true,
            ],
            'attempts' => [
                'title' => __('Attempts', 'usp'),
                'sortable' => true,
            ],
            'available_at' => [
                'title' => __('Available At', 'usp'),
                'sortable' => true,
            ],
            'created_at' => [
                'title' => __('Created At', 'usp'),
                'sortable' => true,
            ],
        ];
    }
}