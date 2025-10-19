<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Service\TemplateManagerInterface;
use UserSpace\Core\StringFilterInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class LoginFormRenderer
{
    public function __construct(
        private readonly TemplateManagerInterface $templateManager,
        private readonly StringFilterInterface    $str
    )
    {
    }

    public function render(): string
    {
        if (is_user_logged_in()) {
            return '<p>' . $this->str->translate('You are already logged in.') . '</p>';
        }

        wp_enqueue_style('usp-form');
        wp_enqueue_script('usp-login-handler');

        return $this->templateManager->render('login_form');
    }
}