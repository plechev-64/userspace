<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Module\Form\App\UseCase\GetLoginForm\GetLoginFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\GetLoginForm\GetLoginFormUseCase;
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

class LoginFormRenderer
{
    public function __construct(
        private readonly TemplateManagerInterface $templateManager,
        private readonly StringFilterInterface    $str,
        private readonly AssetRegistryInterface   $assetRegistry,
        private readonly UserApiInterface         $userApi,
        private readonly GetLoginFormUseCase      $getLoginFormUseCase,
        private readonly PluginSettingsInterface  $optionManager
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

        try {
            $command = new GetLoginFormCommand();
            $result = $this->getLoginFormUseCase->execute($command);
            $form = $result->form;
        } catch (UspException $e) {
            // В случае ошибки (например, конфиг не найден), возвращаем сообщение
            return '<p style="color: red;">' . $this->str->escHtml($e->getMessage()) . '</p>';
        }

        $settings = $this->optionManager->all();

        return $this->templateManager->render('login_form', [
            'form' => $form,
            'settings' => $settings,
        ]);
    }
}