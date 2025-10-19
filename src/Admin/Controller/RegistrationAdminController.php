<?php

namespace UserSpace\Admin\Controller;

use UserSpace\Admin\Controller\Abstract\AbstractAdminFormController;
use UserSpace\Core\Rest\Attributes\Route;

#[Route(path: '/admin/registration-form')]
class RegistrationAdminController extends AbstractAdminFormController
{
    protected function getFormType(): string
    {
        return 'registration';
    }

    protected function processDeletedFields(array $deletedFields): void
    {
        // Для формы регистрации пока нет специфической логики удаления мета-данных
    }
}