<?php

namespace UserSpace\Common\Module\SetupWizard\Infrastructure;

use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectFieldDto;
use UserSpace\Common\Module\SetupWizard\Domain\SetupWizardConfig;
use UserSpace\Common\Module\SetupWizard\Domain\SetupWizardConfigRegistryInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрирует шаги по умолчанию для мастера настройки.
 */
class DefaultSetupWizardConfig
{
    public function __construct(
        private readonly SetupWizardConfigRegistryInterface $registry,
        private readonly StringFilterInterface              $str,
        private readonly HookManagerInterface               $hookManager
    )
    {
    }

    public function register(): void
    {
        $this->registry->register([$this, 'addDefaultSteps']);
    }

    public function addDefaultSteps(SetupWizardConfig $config): void
    {
        $config
            //-- Шаг 1: Основные страницы
            ->addStep('pages', $this->str->translate('Page Assignment'))
            ->addOption(new SelectFieldDto('login_page_id', [
                'label' => $this->str->translate('Login Page'),
                'options' => $this->getPagesAsOptions(),
                'description' => $this->str->translate('Select the page where the login form will be displayed.'),
            ]))
            ->addOption(new SelectFieldDto('registration_page_id', [
                'label' => $this->str->translate('Registration Page'),
                'options' => $this->getPagesAsOptions(),
                'description' => $this->str->translate('Select the page for the user registration form.'),
            ]))
            ->addOption(new SelectFieldDto('profile_page_id', [
                'label' => $this->str->translate('User Profile Page'),
                'options' => $this->getPagesAsOptions(),
                'description' => $this->str->translate('Select the page to display user profiles.'),
            ]))

            //-- Шаг 2: Дополнительные настройки
            ->addStep('advanced', $this->str->translate('Advanced Settings'))
            ->addOption(new SelectFieldDto('password_reset_page_id', [
                'label' => $this->str->translate('Password Recovery Page'),
                'options' => $this->getPagesAsOptions(),
            ]));

        $this->hookManager->applyFilters('usp_setup_wizard_config', $config);
    }

    private function getPagesAsOptions(): array
    {
        $pages = get_pages();
        $options = ['' => $this->str->translate('— Select a page —')];
        foreach ($pages as $page) {
            $options[$page->ID] = $page->post_title;
        }
        return $options;
    }
}