<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminFormPage;
use UserSpace\Core\ContainerInterface;
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

    protected function createDefaultConfig(): array
    {
        // Для примера, используем поля из профиля + добавляем свои
        /** @var ContainerInterface $container */
        $container = Plugin::getInstance()->getContainer();
        /** @var ProfileFormPage $profilePage */
        $profilePage   = $container->get(ProfileFormPage::class);
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