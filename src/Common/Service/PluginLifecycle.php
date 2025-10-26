<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Module\Form\Src\Domain\Repository\FormRepositoryInterface;
use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileRepositoryInterface;
use UserSpace\Common\Module\Queue\Src\Domain\JobRepositoryInterface;
use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Core\Admin\AdminApiInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\Http\Request;
use UserSpace\Core\WpApiInterface;

/**
 * Управляет жизненным циклом плагина (активация, деактивация, удаление).
 */
class PluginLifecycle
{
    private const REDIRECT_TRANSIENT = 'usp_setup_wizard_redirect';

    public function __construct(
        private readonly FormRepositoryInterface          $formRepository,
        private readonly JobRepositoryInterface           $jobRepository,
        private readonly SseEventRepositoryInterface      $sseEventRepository,
        private readonly CronManager                      $cronManager,
        private readonly OptionManagerInterface           $optionManager,
        private readonly WpApiInterface                   $wpApi,
        private readonly AdminApiInterface                $adminApi,
        private readonly HookManagerInterface             $hookManager,
        private readonly TemporaryFileRepositoryInterface $tempFileRepository,
        private readonly Request                          $request
    )
    {
    }

    /**
     * Выполняется при активации плагина.
     *
     * - Устанавливает временный флаг для перенаправления на мастер настройки.
     * - Здесь можно добавить другие действия при активации (например, создание таблиц, сброс правил).
     */
    public function onActivation(): void
    {
        // Устанавливаем флаг для редиректа на 30 секунд.
        $this->optionManager->transient()->set(self::REDIRECT_TRANSIENT, true, 30);

        $this->formRepository->createTable();
        $this->jobRepository->createTable();
        $this->sseEventRepository->createTable();
        $this->tempFileRepository->createTable();

        // Устанавливаем опцию, которая может понадобиться в будущем.
        $this->optionManager->add('userspace_version', USERSPACE_VERSION);

        $this->hookManager->doAction('usp_flush_rewrite_rules'); // Используем хук, чтобы не вызывать напрямую
    }

    /**
     * Выполняется при деактивации плагина.
     */
    public function onDeactivation(): void
    {
        // Удаляем все cron-задачи, связанные с очередью
        $this->cronManager->unregisterAllSchedules();
        $this->formRepository->dropTable();
        $this->jobRepository->dropTable();
        $this->sseEventRepository->dropTable();
        $this->tempFileRepository->dropTable();
        $this->hookManager->doAction('usp_flush_rewrite_rules');
    }

    /**
     * Проверяет флаг и, если он установлен, перенаправляет пользователя
     * на страницу мастера первоначальной настройки.
     *
     * Вызывается на хуке `admin_init`.
     */
    public function redirectOnActivation(): void
    {
        if ($this->optionManager->transient()->get(self::REDIRECT_TRANSIENT)) {
            $this->optionManager->transient()->delete(self::REDIRECT_TRANSIENT);

            // Не перенаправляем при AJAX запросах, CRON, или сетевой активации.
            if ($this->wpApi->isDoingAjax() || $this->wpApi->isDoingCron() || $this->request->getQuery('activate-multi') !== null) {
                return;
            }

            // Безопасное перенаправление.
            $this->wpApi->safeRedirect($this->adminApi->adminUrl('admin.php?page=userspace-setup'));
            exit;
        }
    }
}