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

class LoginFormRenderer
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
            return '<p>' . $this->str->translate('You are already logged in.') . '</p>';
        }

        $this->assetRegistry->enqueueStyle('usp-form');
        $this->assetRegistry->enqueueScript('usp-login-handler');

        return $this->templateManager->render('login_form');
    }
}