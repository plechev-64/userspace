<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Grid\QueueJobsGrid;
use UserSpace\Core\Queue\QueueStatus;

class QueueJobsPage extends AbstractAdminPage
{
    public function __construct(
        private readonly QueueJobsGrid $grid,
        private readonly QueueStatus $status
    ) {
    }

    public function render(): void
    {
        $this->enqueuePageScripts();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->getPageTitle()) . '</h1>';

        $this->renderStatusWidget();

        echo $this->grid->render();

        echo '</div>';
    }

    private function renderStatusWidget(): void
    {
        $status = $this->status->getStatus();
        $state_text = [
            'running' => __('Running', 'usp'),
            'idle' => __('Idle', 'usp'),
            'stalled' => __('Stalled', 'usp'),
        ];
        ?>
        <div class="usp-queue-status-widget">
            <div class="usp-queue-widget-header" id="usp-queue-widget-header">
                <h3><?php esc_html_e('Queue Status', 'usp'); ?></h3>
                <div class="usp-queue-actions">
                    <button type="button" id="usp-process-now-btn" class="button button-secondary" style="margin-right: 10px;"><?php esc_html_e('Process Now', 'usp'); ?></button>
                    <button type="button" id="usp-send-ping-btn" class="button"><?php esc_html_e('Send Ping Task', 'usp'); ?></button>
                </div>
            </div>
            <p id="usp-queue-status-text">
                <span class="usp-queue-status-indicator <?php echo esc_attr($status['state']); ?>"></span>
                <strong><?php echo esc_html($state_text[$status['state']] ?? 'Unknown'); ?></strong>
            </p>
            <h4><?php esc_html_e('Recent Activity', 'usp'); ?></h4>
            <div class="usp-queue-log" id="usp-queue-log">
                <?php if (empty($status['log'])): ?>
                    <?php esc_html_e('No recent activity.', 'usp'); ?>
                <?php else: ?>
                    <?php echo implode("\n", array_map('esc_html', $status['log'])); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function enqueuePageScripts(): void
    {
        wp_enqueue_script(
            'usp-queue-page-script',
            USERSPACE_PLUGIN_URL . 'assets/js/queue-page.js',
            ['usp-core', 'wp-i18n'],
            USERSPACE_VERSION,
            true
        );
        wp_localize_script('usp-queue-page-script', 'uspQueuePageData', [
            'statusEndpoint' => '/queue/status',
            'pingEndpoint'   => '/queue/ping',
            'processEndpoint' => '/queue/process-now',
            'ping_error' => __('Failed to dispatch ping task.', 'usp'),
            'ping_sending' => __('Sending...', 'usp'),
            'processing' => __('Processing...', 'usp'),
        ]);
    }

    public function getPageTitle(): string
    {
        return __('Jobs Queue', 'usp');
    }

    public function getMenuTitle(): string
    {
        return __('Jobs Queue', 'usp');
    }

    protected function getMenuSlug(): string
    {
        return 'userspace-queue-jobs';
    }
    protected function getParentSlug(): ?string
    {
        return 'userspace-settings';
    }
}