<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetRegistrationForm;

use UserSpace\Common\Module\Form\Src\Domain\Factory\FormFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Core\Exception\UspException;

if (!defined('ABSPATH')) {
    exit;
}

class GetRegistrationFormUseCase
{
    public const FORM_TYPE = 'registration';

    public function __construct(
        private readonly FormConfigManagerInterface $formConfigManager,
        private readonly FormFactoryInterface       $formFactory
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(GetRegistrationFormCommand $command): GetRegistrationFormResult
    {
        $formConfig = $this->formConfigManager->load(self::FORM_TYPE);

        if (!$formConfig) {
            throw new UspException('Registration form config not found.', 404);
        }

        $form = $this->formFactory->create($formConfig);

        return new GetRegistrationFormResult($form);
    }
}