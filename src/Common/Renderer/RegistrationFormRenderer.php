<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Service\TemplateManagerInterface;
use UserSpace\Core\Helper\StringFilterInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class RegistrationFormRenderer
{

    public function __construct(
        private readonly FormManager              $formManager,
        private readonly FormFactory              $formFactory,
        private readonly TemplateManagerInterface $templateManager,
        private readonly StringFilterInterface    $str
    )
    {
    }

    public function render(): string
    {
        if (is_user_logged_in()) {
            return '<p>' . $this->str->translate('You are already registered and logged in.') . '</p>';
        }

        $formType = 'registration';
        $config = $this->formManager->load($formType);

        if (null === $config) {
            return '<p style="color: red;">' . $this->str->translate('Registration form is not configured yet.') . '</p>';
        }

        wp_enqueue_style('usp-form');
        wp_enqueue_script('usp-registration-handler');

        // $config уже является DTO, передаем его напрямую в фабрику
        $form = $this->formFactory->create($config);
        $settings = get_option('usp_settings', []);

        return $this->templateManager->render('registration_form', [
            'form' => $form,
            'settings' => $settings,
        ]);
    }
}