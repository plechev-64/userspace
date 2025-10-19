<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminFormPage;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;

class ProfileFormPage extends AbstractAdminFormPage
{
    protected function getPageTitle(): string
    {
        return $this->str->translate('Profile Form Editor');
    }

    protected function getMenuTitle(): string
    {
        return $this->str->translate('Profile Form');
    }

    protected function getMenuSlug(): string
    {
        return 'userspace-profile-form';
    }

    protected function getFormType(): string
    {
        return 'profile';
    }

    protected function createDefaultConfig(): FormConfig
    {
        $this->formBuilder
            ->reset() // Сбрасываем состояние перед построением
            ->addSection('main', $this->str->translate('Main Information'))
            ->addBlock('main_block');
        $this->formBuilder->addField('user_email', [
            'type' => 'text',
            'label' => $this->str->translate('Email'),
            'rules' => ['required' => true],
        ])
            ->addField('display_name', [
                'type' => 'text',
                'label' => $this->str->translate('Display Name'),
                'rules' => ['required' => true],
            ])
            ->addField('about', [
                'type' => 'textarea',
                'label' => $this->str->translate('About Me'),
            ])
            ->addField('gender', [
                'type' => 'radio',
                'label' => $this->str->translate('Gender'),
                'options' => ['male' => $this->str->translate('Male'), 'female' => $this->str->translate('Female')],
            ])
            ->addField('hair_color', [
                'type' => 'select',
                'label' => $this->str->translate('Hair Color'),
                'options' => ['blond' => $this->str->translate('Blond'), 'brunette' => $this->str->translate('Brunette'), 'red' => $this->str->translate('Red')],
            ]);

        return $this->formBuilder->build();
    }
}