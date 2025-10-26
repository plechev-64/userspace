<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Page\Abstract\AbstractAdminFormPage;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Plugin;

class RegistrationFormPage extends AbstractAdminFormPage
{
    protected function getPageTitle(): string
    {
        return __('Registration Form Editor', 'usp');
    }

    protected function getMenuTitle(): string
    {
        return __('Registration Form', 'usp');
    }

    protected function getMenuSlug(): string
    {
        return 'userspace-registration-form';
    }

    protected function getFormType(): string
    {
        return 'registration';
    }

    protected function createDefaultConfig(): FormConfig
    {
        // Для примера, используем поля из профиля + добавляем свои
        $container = Plugin::getInstance()->getContainer();
        $profilePage = $container->get(ProfileFormPage::class);
        $profileConfig = $profilePage->createDefaultConfig();

        // Добавляем обязательные поля для регистрации
        $this->formBuilder->reset()
            ->addSection('main', __('Main Information', 'usp'))
            ->addBlock('main_block');

        // Добавляем все поля из профиля как доступные
        $profileFields = $this->getFieldsFromConfig($profileConfig);
        foreach ($profileFields as $name => $config) {
            $this->formBuilder->addField($name, $config);
        }

        // Добавляем постоянное поле "повтор пароля"
        $this->formBuilder->addField('password_repeat', [
            'type' => 'text', // В идеале 'password'
            'label' => __('Repeat Password', 'usp'),
            'rules' => ['required' => true],
        ]);

        return $this->formBuilder->build();
    }
}