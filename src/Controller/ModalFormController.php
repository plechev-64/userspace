<?php

namespace UserSpace\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Service\ShortcodeManager;

class ModalFormController extends AbstractController
{

    #[Route(path: '/modal-form/(?P<type>[a-zA-Z0-9_-]+)', method: 'GET')]
    public function getFormHtml(string $type): JsonResponse
    {
        $shortcodeMap = [
            'login'            => '[usp_login_form]',
            'registration'     => '[usp_registration_form]',
            'forgot-password'  => '[usp_forgot_password_form]',
        ];

        if (!isset($shortcodeMap[$type])) {
            return $this->error(['message' => 'Form type not found.'], 404);
        }

        // Используем do_shortcode для рендеринга HTML
        $html = do_shortcode($shortcodeMap[$type]);

        return $this->success([
            'html' => $html,
        ]);
    }
}