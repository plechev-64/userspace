<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TemplateManagerInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class ForgotPasswordFormRenderer
{
    public function __construct(
        private readonly TemplateManagerInterface $templateManager,
        private readonly StringFilterInterface    $str,
        private readonly AssetRegistryInterface   $assetRegistry,
        private readonly UserApiInterface         $userApi
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

        return $this->templateManager->render('forgot_password_form');
    }
}