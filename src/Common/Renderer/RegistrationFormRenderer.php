<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TemplateManagerInterface;
use UserSpace\Core\User\UserApiInterface;

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
        private readonly StringFilterInterface    $str,
        private readonly OptionManagerInterface   $optionManager,
        private readonly AssetRegistryInterface   $assetRegistry,
        private readonly UserApiInterface         $userApi
    )
    {
    }

    public function render(): string
    {
        if ($this->userApi->isUserLoggedIn()) {
            return '<p>' . $this->str->translate('You are already registered and logged in.') . '</p>';
        }

        $formType = 'registration';
        $config = $this->formManager->load($formType);

        if (null === $config) {
            return '<p style="color: red;">' . $this->str->translate('Registration form is not configured yet.') . '</p>';
        }

        $this->assetRegistry->enqueueStyle('usp-form');
        $this->assetRegistry->enqueueScript('usp-registration-handler');

        // $config уже является DTO, передаем его напрямую в фабрику
        $form = $this->formFactory->create($config);
        $settings = $this->optionManager->get('usp_settings', []);

        return $this->templateManager->render('registration_form', [
            'form' => $form,
            'settings' => $settings,
        ]);
    }
}