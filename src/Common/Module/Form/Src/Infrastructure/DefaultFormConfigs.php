<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure;

use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\Config\FormConfigBuilder;
use UserSpace\Core\String\StringFilterInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрирует конфигурации форм по умолчанию (логин, восстановление пароля).
 */
class DefaultFormConfigs
{
    public function __construct(
        private readonly FormConfigBuilder          $formConfigBuilder,
        private readonly FormConfigManagerInterface $formConfigManager,
        private readonly StringFilterInterface      $str
    )
    {
    }

    public function register(): void
    {
        $this->formConfigManager->registerInternalConfig('login', [$this, 'createLoginConfig']);
        $this->formConfigManager->registerInternalConfig('forgot-password', [$this, 'createForgottenPassFormConfig']);
    }

    /**
     * Создает конфигурацию для формы входа.
     */
    public function createLoginConfig(): FormConfig
    {
        $this->formConfigBuilder->reset();
        $this->formConfigBuilder->addSection('main_section')->addBlock('main_block');

        $this->formConfigBuilder->addField('log', [
            'type' => 'text',
            'label' => $this->str->translate('Username or Email Address'),
            'rules' => [
                'required' => true,
            ],
            'attributes' => [
                'size' => 20,
            ]
        ]);

        $this->formConfigBuilder->addField('pwd', [
            'type' => 'password',
            'label' => $this->str->translate('Password'),
            'rules' => [
                'required' => true,
            ],
            'attributes' => [
                'size' => 20,
            ]
        ]);

        $this->formConfigBuilder->addField('rememberme', [
            'type' => 'boolean',
            'label' => $this->str->translate('Remember Me'),
            'value' => 'forever',
        ]);

        return $this->formConfigBuilder->build();
    }

    public function createForgottenPassFormConfig(): FormConfig
    {

        $this->formConfigBuilder->reset();
        $this->formConfigBuilder->addSection('main_section')->addBlock('main_block');

        $this->formConfigBuilder->addField('user_login', [
            'type' => 'text',
            'label' => $this->str->translate('Username or Email Address'),
            'rules' => [
                'required' => true,
            ],
            'attributes' => [
                'size' => 20,
            ]
        ]);

        return $this->formConfigBuilder->build();
    }
}