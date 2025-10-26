<?php

namespace UserSpace\Common\Module\Form\App\UseCase\SaveConfig;

use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueDispatcher;
use UserSpace\Common\Module\User\App\Task\Message\DeleteUserMetaMessage;

class SaveProfileFormConfigUseCase
{
    private const FORM_TYPE = 'profile';

    public function __construct(
        private readonly FormConfigManagerInterface $formManager,
        private readonly QueueDispatcher            $queueDispatcher
    )
    {
    }

    public function execute(SaveFormConfigCommand $command): void
    {
        if (!empty($command->deletedFields)) {
            $this->processDeletedFields($command->deletedFields);
        }

        $this->formManager->save(self::FORM_TYPE, $command->formConfig);
    }

    /**
     * Обрабатывает удаление мета-данных для удаленных полей формы профиля.
     */
    private function processDeletedFields(array $deletedFields): void
    {
        // Отправляем ресурсоемкую задачу в очередь для фонового выполнения.
        $message = new DeleteUserMetaMessage($deletedFields);
        $this->queueDispatcher->dispatch($message);
    }
}