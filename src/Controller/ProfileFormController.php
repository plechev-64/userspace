<?php

namespace UserSpace\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Form\FormFactory;
use UserSpace\Form\FormManager;

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
        private readonly FormManager $formManager,
        private readonly FormFactory $formFactory
    ) {
    }

    #[Route(path: '/save', method: 'POST')]
    public function saveProfile(Request $request): JsonResponse
    {
        $formType = 'profile';
        $config = $this->formManager->load($formType);

        if (null === $config) {
            return $this->error(['message' => __('Form configuration not found.', 'usp')], 404);
        }

        // Обновляем DTO данными из запроса, не пересобирая его
        $fields = $config->getFields();
        foreach (array_keys($fields) as $fieldName) {
            $postValue = $request->getPost($fieldName);
            if ($postValue !== null) {
                // Здесь можно добавить более сложную санацию в зависимости от типа поля
                $config->updateFieldValue($fieldName, wp_unslash($postValue));
            }
        }

        $form = $this->formFactory->create($config);

        if ($form->validate()) {
            $userId = get_current_user_id();
            if (!$userId) {
                return $this->error(['message' => __('You must be logged in to save the profile.', 'usp')], 401);
            }

            $coreData = ['ID' => $userId];
            $metaData = [];

            foreach ($form->getFields() as $field) {
                $fieldName = $field->getName();
                $fieldValue = $field->getValue();

                if (in_array($fieldName, $this->coreUserFields, true)) {
                    $coreData[$fieldName] = $fieldValue;
                } else {
                    $metaData[$fieldName] = $fieldValue;
                }
            }

            // Сохраняем основные данные пользователя
            if (count($coreData) > 1) {
                wp_update_user($coreData);
            }

            // Сохраняем мета-данные
            foreach ($metaData as $key => $value) {
                update_user_meta($userId, $key, $value);
            }

            return $this->success(['message' => __('Data saved successfully!', 'usp')]);
        }

        return $this->error([
            'message' => __('Validation error. Please check the fields.', 'usp'),
            'errors' => $form->getErrors(),
        ], 422); // 422 Unprocessable Entity - стандарт для ошибок валидации
    }
}