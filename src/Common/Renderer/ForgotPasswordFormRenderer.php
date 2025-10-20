<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Service\TemplateManagerInterface;
use UserSpace\Core\AssetRegistryInterface;
use UserSpace\Core\StringFilterInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class ForgotPasswordFormRenderer
{
    public function __construct(
        private readonly TemplateManagerInterface $templateManager,
        private readonly StringFilterInterface    $str,
        private readonly AssetRegistryInterface   $assetRegistry
    )
    {
    }

    public function render(): string
    {
        if (is_user_logged_in()) {
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

        return $this->templateManager->render('forgot_password_form');
    }
}