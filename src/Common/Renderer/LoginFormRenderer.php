<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Service\TemplateManagerInterface;
use UserSpace\Core\AssetRegistryInterface;
use UserSpace\Core\StringFilterInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class LoginFormRenderer
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
            return '<p>' . $this->str->translate('You are already logged in.') . '</p>';
        }

        $this->assetRegistry->enqueueStyle('usp-form');
        $this->assetRegistry->enqueueScript('usp-login-handler');

        return $this->templateManager->render('login_form');
    }
}