<?php

namespace UserSpace\Service;

use UserSpace\Core\Theme\ThemeManager;
use UserSpace\Renderer\ForgotPasswordFormRenderer;
use UserSpace\Renderer\LoginFormRenderer;
use UserSpace\Renderer\RegistrationFormRenderer;

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
        private readonly ThemeManager               $themeManager,
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
    public function renderForm( array $atts ): string
    {
		$atts = shortcode_atts( [
			'type' => '',
		], $atts, 'usp_form' );
		return $this->genericFormRenderer->render( $atts );
    }

    public function renderLoginForm( array $atts ): string
    {
		return $this->loginFormRenderer->render();
    }

    public function renderRegistrationForm( array $atts ): string
    {
		return $this->registrationFormRenderer->render();
    }

    public function renderForgotPasswordForm( array $atts ): string
    {
		return $this->forgotPasswordFormRenderer->render();
    }
}