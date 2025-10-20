<?php

namespace UserSpace\Common\Module\Grid\Src\Infrastructure;

use UserSpace\Common\Module\Grid\Src\Domain\TableContentGrid;
use UserSpace\Core\Database\DatabaseConnectionInterface;
use UserSpace\Core\StringFilterInterface;

class QueueJobsGrid extends TableContentGrid
{
    public function __construct(
        DatabaseConnectionInterface $db,
        StringFilterInterface $str
    ) {
        parent::__construct($db, $str);
    }

    public function render(): string
    {
        $this->registerAssets();

        return parent::render();
    }

    public function getEndpointPath(): string
    {
        return '/grid/queue-jobs';
    }

    protected function getTableName(): string
    {
        return $this->db->getTableName('userspace_jobs');
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
                'title' => $this->str->translate('ID'),
                'sortable' => true,
            ],
            'message_class' => [
                'title' => $this->str->translate('Message Class'),
                'sortable' => true,
            ],
            'status' => [
                'title' => $this->str->translate('Status'),
                'sortable' => true,
            ],
            'attempts' => [
                'title' => $this->str->translate('Attempts'),
                'sortable' => true,
            ],
            'available_at' => [
                'title' => $this->str->translate('Available At'),
                'sortable' => true,
            ],
            'created_at' => [
                'title' => $this->str->translate('Created At'),
                'sortable' => true,
            ],
        ];
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
            'loading' => $this->str->translate('Loading...'),
            'error' => $this->str->translate('An error occurred. Please try again.'),
        ]]);
    }
}