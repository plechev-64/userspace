<?php

namespace UserSpace\Admin\Page;

use UserSpace\Admin\Abstract\AbstractAdminFormPage;

class ProfileFormPage extends AbstractAdminFormPage
{
    protected function getPageTitle(): string
    {
        return __('Profile Form Editor', 'usp');
    }

    protected function getMenuTitle(): string
    {
        return __('Profile Form', 'usp');
    }

    protected function getMenuSlug(): string
    {
        return 'userspace-profile-form';
    }

    protected function getFormType(): string
    {
        return 'profile';
    }

    protected function createDefaultConfig(): array
    {
        $this->formBuilder
            ->reset() // Сбрасываем состояние перед построением
            ->addSection('main', __('Main Information', 'usp'))
            ->addBlock('main_block');
        $this->formBuilder->addField('user_email', [
                'type'  => 'text',
            'label' => __('Email', 'usp'),
                'rules' => [ 'required' => true ],
        ])
            ->addField('display_name', [
                'type'  => 'text',
                'label' => __('Display Name', 'usp'),
                'rules' => [ 'required' => true ],
            ])
            ->addField('about', [
                'type'  => 'textarea',
                'label' => __('About Me', 'usp'),
            ])
            ->addField('gender', [
                'type'    => 'radio',
                'label'   => __('Gender', 'usp'),
                'options' => ['male' => __('Male', 'usp'), 'female' => __('Female', 'usp')],
            ])
            ->addField('hair_color', [
                'type'    => 'select',
                'label'   => __('Hair Color', 'usp'),
                'options' => ['blond' => __('Blond', 'usp'), 'brunette' => __('Brunette', 'usp'), 'red' => __('Red', 'usp')],
            ]);

        return $this->formBuilder->build();
    }
}