<?php

namespace UserSpace\Common\Module\Form\App\UseCase\SaveProfileForm;

use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Common\Module\Media\Src\Domain\TemporaryFileRepositoryInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class SaveProfileFormUseCase
{
    /**
     * Поля, которые обновляются в таблице wp_users.
     * Все остальные сохраняются в wp_usermeta.
     * @var string[]
     */
    private const CORE_USER_FIELDS = ['user_email', 'display_name', 'user_url', 'user_pass', 'nickname'];

    public function __construct(
        private readonly FormManager                      $formManager,
        private readonly FormFactory                      $formFactory,
        private readonly StringFilterInterface            $str,
        private readonly UserApiInterface                 $userApi,
        private readonly TemporaryFileRepositoryInterface $tempFileRepository
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(SaveProfileFormCommand $command): void
    {
        $config = $this->formManager->load($command->formType);

        if (null === $config) {
            throw new UspException($this->str->translate('Form configuration not found.'), 404);
        }

        // Обновляем DTO данными из запроса
        $fields = $config->getFields();
        foreach (array_keys($fields) as $fieldName) {
            if (array_key_exists($fieldName, $command->fieldsWithValues)) {
                $config->updateFieldValue($fieldName, $this->str->unslash($command->fieldsWithValues[$fieldName]));
            }
        }

        $form = $this->formFactory->create($config);

        if (!$form->validate()) {
            throw new UspException(
                $this->str->translate('Validation error. Please check the fields.'),
                422,
                ['errors' => $form->getErrors()]
            );
        }

        $userId = $this->userApi->getCurrentUserId();
        if (!$userId) {
            throw new UspException($this->str->translate('You must be logged in to save the profile.'), 401);
        }

        $coreData = ['ID' => $userId];
        $metaData = [];
        $attachmentIds = [];

        foreach ($form->getFields() as $field) {
            $fieldName = $field->getName();
            $fieldValue = $field->getValue();

            if (in_array($fieldName, self::CORE_USER_FIELDS, true)) {
                // Не добавляем пустые значения для полей пароля
                if ($fieldName === 'user_pass' && empty($fieldValue)) {
                    continue;
                }
                $coreData[$fieldName] = $fieldValue;
            } else {
                $metaData[$fieldName] = $fieldValue;
            }

            // Собираем ID файлов для "коммита"
            if (is_numeric($fieldValue) && (int)$fieldValue > 0) {
                $attachmentIds[] = (int)$fieldValue;
            } elseif (is_array($fieldValue)) {
                foreach ($fieldValue as $id) {
                    if (is_numeric($id) && (int)$id > 0) {
                        $attachmentIds[] = (int)$id;
                    }
                }
            }
        }

        // Сохраняем основные данные пользователя
        if (count($coreData) > 1) {
            $this->userApi->updateUser($coreData);
        }

        // Сохраняем мета-данные
        foreach ($metaData as $key => $value) {
            $this->userApi->updateUserMeta($userId, $key, $value);
        }

        // Удаляем использованные файлы из временной таблицы
        if (!empty($attachmentIds)) {
            $this->tempFileRepository->remove($attachmentIds);
        }
    }
}