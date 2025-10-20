<?php

namespace UserSpace\Admin\Controller;

use UserSpace\Admin\Controller\Abstract\AbstractAdminFormController;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/admin/profile-form')]
class ProfileAdminController extends AbstractAdminFormController
{
    public function __construct(
        private readonly StringFilterInterface $str,
        protected readonly FormManager         $formManager
    )
    {
        parent::__construct($formManager);
    }

    protected function getFormType(): string
    {
        return 'profile';
    }

    protected function processDeletedFields(array $deletedFields): void
    {
        // Удаляем мета-данные для удаленных кастомных полей
        foreach ($deletedFields as $meta_key) {
            // Эта логика теперь находится в правильном, специфичном для профиля, месте
            delete_metadata('user', 0, $this->str->sanitizeKey($meta_key), '', true);
        }
    }
}