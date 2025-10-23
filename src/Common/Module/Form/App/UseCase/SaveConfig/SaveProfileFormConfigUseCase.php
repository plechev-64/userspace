<?php

namespace UserSpace\Common\Module\Form\App\UseCase\SaveConfig;

use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\User\UserApiInterface;

class SaveProfileFormConfigUseCase
{
    private const FORM_TYPE = 'profile';

    public function __construct(
        private readonly FormManager      $formManager,
        private readonly UserApiInterface $userApi
    )
    {
    }

    /**
     * @throws UspException
     */
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
        // Для формы профиля, при удалении поля, мы можем захотеть
        // удалить соответствующие мета-данные у всех пользователей.
        // Это ресурсоемкая операция, поэтому ее следует выполнять в фоновом режиме.
        // Пока что, для примера, мы просто удалим мета-данные.
        foreach ($deletedFields as $fieldName) {
            $this->userApi->deleteMetaFromAllUsers($fieldName);
        }
    }
}