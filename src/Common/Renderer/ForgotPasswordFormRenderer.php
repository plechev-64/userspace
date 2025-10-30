<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Module\Form\App\UseCase\GetForgotPasswordForm\GetForgotPasswordFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\GetForgotPasswordForm\GetForgotPasswordFormUseCase;
use UserSpace\Common\Module\Settings\Src\Domain\PluginSettingsInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TemplateManagerInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class ForgotPasswordFormRenderer
{
    public function __construct(
        private readonly TemplateManagerInterface     $templateManager,
        private readonly StringFilterInterface        $str,
        private readonly AssetRegistryInterface       $assetRegistry,
        private readonly UserApiInterface             $userApi,
        private readonly GetForgotPasswordFormUseCase $getRegistrationFormUseCase,
        private readonly PluginSettingsInterface      $optionManager
    )
    {
    }

    public function render(): string
    {
        if ($this->userApi->isUserLoggedIn()) {
            return ''; // Ничего не показываем авторизованным пользователям
        }

        $this->assetRegistry->enqueueStyle('usp-form');
        $this->assetRegistry->enqueueScript('usp-forgot-password-handler');
        $this->assetRegistry->localizeScript(
            'usp-forgot-password-handler',
            'uspL10n',
            [
                'forgotPassword' => [
                    'processing' => $this->str->translate('Processing...'),
                ],
            ]
        );

        try {
            $command = new GetForgotPasswordFormCommand();
            $result = $this->getRegistrationFormUseCase->execute($command);
            $form = $result->form;
        } catch (UspException $e) {
            // В случае ошибки (например, конфиг не найден), возвращаем сообщение
            return '<p style="color: red;">' . $this->str->escHtml($e->getMessage()) . '</p>';
        }

        $settings = $this->optionManager->all();

        return $this->templateManager->render('forgot_password_form', [
            'form' => $form,
            'settings' => $settings,
        ]);
    }
}