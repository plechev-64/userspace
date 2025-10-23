<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetModalForm;

use UserSpace\Common\Renderer\ForgotPasswordFormRenderer;
use UserSpace\Common\Renderer\LoginFormRenderer;
use UserSpace\Common\Renderer\RegistrationFormRenderer;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class GetModalFormUseCase
{
    public function __construct(
        private readonly StringFilterInterface      $str,
        private readonly LoginFormRenderer          $loginFormRenderer,
        private readonly RegistrationFormRenderer   $registrationFormRenderer,
        private readonly ForgotPasswordFormRenderer $forgotPasswordFormRenderer
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(GetModalFormCommand $command): GetModalFormResult
    {
        $renderer = match ($command->formType) {
            'login' => $this->loginFormRenderer,
            'register' => $this->registrationFormRenderer,
            'forgot-password' => $this->forgotPasswordFormRenderer,
            default => null
        };

        if (!$renderer) {
            throw new UspException($this->str->translate('Invalid form type specified.'), 400);
        }

        $html = $renderer->render();

        return new GetModalFormResult($html);
    }
}