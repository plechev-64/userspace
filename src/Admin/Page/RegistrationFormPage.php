<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Page\Abstract\AbstractAdminFormPage;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Plugin;

class RegistrationFormPage extends AbstractAdminFormPage
{
    protected function getPageTitle(): string
    {
        return $this->str->translate('Registration Form Editor');
    }

    protected function getMenuTitle(): string
    {
        return $this->str->translate('Registration Form', 'usp');
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
            ->addSection('main', $this->str->translate('Main Information'))
            ->addBlock('main_block');

        $this->formBuilder->addField('user_login', [
            'type' => 'text',
            'label' => $this->str->translate('Login'),
            'rules' => ['required' => true],
        ]);

        // Добавляем все поля из профиля как доступные
        $profileFields = $this->getFieldsFromConfig($profileConfig);
        foreach ($profileFields as $name => $config) {
            $this->formBuilder->addField($name, $config);
        }

        return $this->formBuilder->build();
    }
}