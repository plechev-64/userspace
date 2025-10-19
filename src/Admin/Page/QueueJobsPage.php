<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminPage;
use UserSpace\Common\Module\Grid\Src\Infrastructure\QueueJobsGrid;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueStatus;
use UserSpace\Core\StringFilterInterface;

class QueueJobsPage extends AbstractAdminPage
{
    public function __construct(
        private readonly QueueJobsGrid         $grid,
        private readonly QueueStatus           $status,
        private readonly StringFilterInterface $str
    )
    {
    }

    public function render(): void
    {
        $this->enqueuePageScripts();

        echo '<div class="wrap">';
        echo '<h1>' . $this->str->escHtml($this->getPageTitle()) . '</h1>';

        $this->renderStatusWidget();

        echo $this->grid->render();

        echo '</div>';
    }

    private function renderStatusWidget(): void
    {
        $status = $this->status->getStatus();
        $state_text = [
            'running' => $this->str->translate('Running'),
            'idle' => $this->str->translate('Idle'),
            'stalled' => $this->str->translate('Stalled'),
        ];
        ?>
        <div class="usp-queue-status-widget">
            <div class="usp-queue-widget-header" id="usp-queue-widget-header">
                <h3><?php echo $this->str->translate('Queue Status'); ?></h3>
                <div class="usp-queue-actions">
                    <button type="button" id="usp-process-now-btn" class="button button-secondary"
                            style="margin-right: 10px;"><?php echo $this->str->translate('Process Now'); ?></button>
                    <button type="button" id="usp-send-ping-btn"
                            class="button"><?php echo $this->str->translate('Send Ping Task'); ?></button>
                </div>
            </div>
            <p id="usp-queue-status-text">
                <span class="usp-queue-status-indicator <?php echo $this->str->escAttr($status['state']); ?>"></span>
                <strong><?php echo $this->str->escHtml($state_text[$status['state']] ?? 'Unknown'); ?></strong>
            </p>
            <h4><?php echo $this->str->translate('Recent Activity'); ?></h4>
            <div class="usp-queue-log" id="usp-queue-log">
                <?php if (empty($status['log'])): ?>
                    <?php echo $this->str->translate('No recent activity.'); ?>
                <?php else: ?>
                    <?php echo implode("\n", array_map([$this->str, 'escHtml'], $status['log'])); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function enqueuePageScripts(): void
    {
        wp_enqueue_style(
            'usp-queue-page-style',
            USERSPACE_PLUGIN_URL . 'assets/css/queue-page.css',
            [],
            USERSPACE_VERSION
        );
        wp_enqueue_script(
            'usp-queue-page-script',
            USERSPACE_PLUGIN_URL . 'assets/js/queue-page.js',
            ['usp-core', 'wp-i18n'],
            USERSPACE_VERSION,
            true
        );
        wp_localize_script('usp-queue-page-script', 'uspQueuePageData', [
            'statusEndpoint' => '/queue/status',
            'pingEndpoint' => '/queue/ping',
            'processEndpoint' => '/queue/process-now',
            'eventsEndpoint' => '/sse/events',
            'ping_error' => $this->str->translate('Failed to dispatch ping task.'),
            'ping_sending' => $this->str->translate('Sending...'),
            'processing' => $this->str->translate('Processing...'),
        ]);
    }

    public function getPageTitle(): string
    {
        return $this->str->translate('Jobs Queue');
    }

    public function getMenuTitle(): string
    {
        return $this->str->translate('Jobs Queue');
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