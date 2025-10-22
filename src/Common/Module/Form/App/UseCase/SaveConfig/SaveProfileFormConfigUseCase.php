<?php

namespace UserSpace\Common\Module\Form\App\UseCase\SaveConfig;

use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\User\UserApiInterface;

class SaveProfileFormConfigUseCase
{
    private const FORM_TYPE = 'profile';

    public function __construct(
        private readonly FormManager           $formManager,
        private readonly UserApiInterface      $userApi,
        private readonly StringFilterInterface $str
    ) {
    }

    /**
     * @throws UspException
     */
    public function execute(SaveFormConfigCommand $command): void
    {
        $configArray = json_decode($command->configJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UspException($this->str->translate('Invalid JSON format.'), 400);
        }

        $deletedFields = json_decode($command->deletedFieldsJson, true);
        if (is_array($deletedFields) && !empty($deletedFields)) {
            $this->processDeletedFields($deletedFields);
        }

        $formConfig = FormConfig::fromArray($configArray);
        $this->formManager->save(self::FORM_TYPE, $formConfig);
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