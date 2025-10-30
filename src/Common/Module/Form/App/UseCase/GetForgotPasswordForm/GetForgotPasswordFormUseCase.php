<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetForgotPasswordForm;

use UserSpace\Common\Module\Form\Src\Domain\Factory\FormFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Core\Exception\UspException;

if (!defined('ABSPATH')) {
    exit;
}

class GetForgotPasswordFormUseCase
{
    public const FORM_TYPE = 'forgot-password';

    public function __construct(
        private readonly FormConfigManagerInterface $formConfigManager,
        private readonly FormFactoryInterface       $formFactory
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(GetForgotPasswordFormCommand $command): GetForgotPasswordFormResult
    {
        $formConfig = $this->formConfigManager->load(self::FORM_TYPE);

        if (!$formConfig) {
            throw new UspException('Forgot password form config not found.', 404);
        }

        $form = $this->formFactory->create($formConfig);

        return new GetForgotPasswordFormResult($form);
    }
}