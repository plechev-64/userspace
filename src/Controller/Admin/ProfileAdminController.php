<?php

namespace UserSpace\Controller\Admin;

use UserSpace\Controller\Admin\Abstract\AbstractAdminFormController;
use UserSpace\Core\Rest\Attributes\Route;

#[Route(path: '/admin/profile-form')]
class ProfileAdminController extends AbstractAdminFormController
{
    protected function getFormType(): string
    {
        return 'profile';
    }

    protected function processDeletedFields(array $deletedFields): void
    {
        // Удаляем мета-данные для удаленных кастомных полей
        foreach ($deletedFields as $meta_key) {
            // Эта логика теперь находится в правильном, специфичном для профиля, месте
            delete_metadata('user', 0, sanitize_key($meta_key), '', true);
        }
    }
}