<?php

namespace UserSpace\Common\Controller;

use UserSpace\Common\Renderer\ForgotPasswordFormRenderer;
use UserSpace\Common\Renderer\LoginFormRenderer;
use UserSpace\Common\Renderer\RegistrationFormRenderer;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

class ModalFormController extends AbstractController
{

    public function __construct(
        private readonly StringFilterInterface      $str,
        private readonly LoginFormRenderer          $loginFormRenderer,
        private readonly RegistrationFormRenderer   $registrationFormRenderer,
        private readonly ForgotPasswordFormRenderer $forgotPasswordFormRenderer
    )
    {
    }

    #[Route(path: '/modal-form/(?P<type>[a-zA-Z0-9_-]+)', method: 'GET')]
    public function getFormHtml(string $type): JsonResponse
    {
        $renderer = match ($type) {
            'login' => $this->loginFormRenderer,
            'register' => $this->registrationFormRenderer,
            'forgot-password' => $this->forgotPasswordFormRenderer,
            default => null
        };

        if (!$renderer) {
            return $this->error(['message' => $this->str->translate('Invalid form type specified.')], 400);
        }

        $html = $renderer->render();

        return $this->success(['html' => $html]);
    }
}