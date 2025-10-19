<?php

namespace UserSpace\Controller\Admin\Abstract;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Module\Form\Src\Infrastructure\FormManager;

abstract class AbstractAdminFormController extends AbstractController
{
    public function __construct(
        protected readonly FormManager $formManager
    ) {
    }

    #[Route(path: '/config', method: 'POST', permission: 'manage_options')]
    final public function saveConfig(Request $request): JsonResponse
    {
        $configJson = $request->getPost('config');
        $deletedFields = json_decode($request->getPost('deleted_fields', '[]'), true);
        $configArray = json_decode($configJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(['message' => __('Invalid JSON format.', 'usp')], 400);
        }

        // Вызываем специфичную для дочернего класса логику обработки удаленных полей
        if (is_array($deletedFields) && !empty($deletedFields)) {
            $this->processDeletedFields($deletedFields);
        }

        $formConfig = FormConfig::fromArray($configArray);
        $this->formManager->save($this->getFormType(), $formConfig);

        return $this->success(['message' => __('Configuration saved successfully.', 'usp')]);
    }

    /**
     * Дочерний класс должен реализовать этот метод для обработки удаленных полей.
     * @param array $deletedFields
     */
    abstract protected function processDeletedFields(array $deletedFields): void;

    /**
     * Дочерний класс должен вернуть свой уникальный тип формы.
     * @return string
     */
    abstract protected function getFormType(): string;
}