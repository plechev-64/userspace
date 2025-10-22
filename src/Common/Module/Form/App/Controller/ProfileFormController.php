<?php

namespace UserSpace\Common\Module\Form\App\Controller;

use UserSpace\Common\Repository\TemporaryFileRepositoryInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\User\UserApiInterface;

#[Route(path: '/profile')]
class ProfileFormController extends AbstractController
{
    /**
     * Поля, которые обновляются в таблице wp_users.
     * Все остальные сохраняются в wp_usermeta.
     * @var string[]
     */
    private array $coreUserFields = ['user_email', 'display_name', 'user_url', 'user_pass', 'nickname'];

    public function __construct(
        private readonly FormManager           $formManager,
        private readonly FormFactory           $formFactory,
        private readonly StringFilterInterface $str,
        private readonly UserApiInterface      $userApi,
        private readonly TemporaryFileRepositoryInterface $tempFileRepository
    )
    {
    }

    #[Route(path: '/save', method: 'POST')]
    public function saveProfile(Request $request): JsonResponse
    {
        $formType = 'profile';
        $config = $this->formManager->load($formType);

        if (null === $config) {
            return $this->error(['message' => $this->str->translate('Form configuration not found.')], 404);
        }

        // Обновляем DTO данными из запроса, не пересобирая его
        $fields = $config->getFields();
        foreach (array_keys($fields) as $fieldName) {
            $postValue = $request->getPost($fieldName);
            if ($postValue !== null) {
                // Здесь можно добавить более сложную санацию в зависимости от типа поля
                $config->updateFieldValue($fieldName, $this->str->unslash($postValue));
            }
        }

        $form = $this->formFactory->create($config);

        if ($form->validate()) {
            $userId = $this->userApi->getCurrentUserId();
            if (!$userId) {
                return $this->error(['message' => $this->str->translate('You must be logged in to save the profile.')], 401);
            }

            $coreData = ['ID' => $userId];
            $metaData = [];
            $attachmentIds = [];

            foreach ($form->getFields() as $field) {
                $fieldName = $field->getName();
                $fieldValue = $field->getValue();

                if (in_array($fieldName, $this->coreUserFields, true)) {
                    $coreData[$fieldName] = $fieldValue;
                } else {
                    $metaData[$fieldName] = $fieldValue;
                }

                // Собираем ID файлов для "коммита"
                if (is_numeric($fieldValue) && (int)$fieldValue > 0) {
                    $attachmentIds[] = (int)$fieldValue;
                } elseif (is_array($fieldValue)) {
                    foreach ($fieldValue as $id) {
                        if (is_numeric($id) && (int)$id > 0) $attachmentIds[] = (int)$id;
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

            return $this->success(['message' => $this->str->translate('Data saved successfully!')]);
        }

        return $this->error([
            'message' => $this->str->translate('Validation error. Please check the fields.'),
            'errors' => $form->getErrors(),
        ], 422); // 422 Unprocessable Entity - стандарт для ошибок валидации
    }
}