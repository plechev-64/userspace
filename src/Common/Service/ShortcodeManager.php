<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Module\Grid\Src\Infrastructure\UserListGrid;
use UserSpace\Common\Renderer\ForgotPasswordFormRenderer;
use UserSpace\Common\Renderer\GenericFormRenderer;
use UserSpace\Common\Renderer\LoginFormRenderer;
use UserSpace\Common\Renderer\RegistrationFormRenderer;
use UserSpace\Core\Theme\ThemeManagerInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Управляет регистрацией и рендерингом шорткодов плагина.
 */
class ShortcodeManager
{

    public function __construct(
        private readonly LoginFormRenderer          $loginFormRenderer,
        private readonly RegistrationFormRenderer   $registrationFormRenderer,
        private readonly ForgotPasswordFormRenderer $forgotPasswordFormRenderer,
        private readonly ThemeManagerInterface      $themeManager,
        private readonly GenericFormRenderer        $genericFormRenderer,
        private readonly UserListGrid               $userListGrid
    )
    {
    }

    /**
     * Регистрирует все шорткоды плагина.
     */
    public function registerShortcodes(): void
    {
        add_shortcode('usp_form', [$this, 'renderForm']);
        add_shortcode('usp_login_form', [$this, 'renderLoginForm']);
        add_shortcode('usp_forgot_password_form', [$this, 'renderForgotPasswordForm']);
        add_shortcode('usp_registration_form', [$this, 'renderRegistrationForm']);
        add_shortcode('usp_account', [$this, 'renderAccount']);
        add_shortcode('usp_users', [$this, 'renderUsers']);
    }

    public function renderAccount(): string
    {
        return $this->themeManager->renderActiveTheme();
    }

    /**
     * Callback-функция для шорткода [usp_form].
     *
     * @param array $atts Атрибуты шорткода.
     *
     * @return string HTML-код формы.
     */
    public function renderForm(array $atts): string
    {
        $atts = shortcode_atts([
            'type' => '',
        ], $atts, 'usp_form');
        return $this->genericFormRenderer->render($atts);
    }

    public function renderLoginForm(array $atts): string
    {
        return $this->loginFormRenderer->render();
    }

    public function renderRegistrationForm(array $atts): string
    {
        return $this->registrationFormRenderer->render();
    }

    public function renderForgotPasswordForm(array $atts): string
    {
        return $this->forgotPasswordFormRenderer->render();
    }

    public function renderUsers(array $atts): string
    {
        return $this->userListGrid->render();
    }
}