<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetLoginForm;

use UserSpace\Common\Module\Form\Src\Domain\Factory\FormFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Core\Exception\UspException;

if (!defined('ABSPATH')) {
    exit;
}

class GetLoginFormUseCase
{
    public const FORM_TYPE = 'login';

    public function __construct(
        private readonly FormConfigManagerInterface $formConfigManager,
        private readonly FormFactoryInterface       $formFactory
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(GetLoginFormCommand $command): GetLoginFormResult
    {
        $formConfig = $this->formConfigManager->load(self::FORM_TYPE);

        if (!$formConfig) {
            throw new UspException('Login form config not found.', 404);
        }

        $form = $this->formFactory->create($formConfig);

        return new GetLoginFormResult($form);
    }
}