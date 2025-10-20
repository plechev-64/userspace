<?php

namespace UserSpace\Common\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\User\UserApiInterface;
use UserSpace\Core\WpApiInterface;

class PasswordResetController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface $str,
        private readonly UserApiInterface      $userApi,
        private readonly WpApiInterface        $wpApi
    )
    {
    }

    #[Route(path: '/password/reset', method: 'POST')]
    public function handlePasswordReset(Request $request): JsonResponse
    {
        $userLogin = $request->getPost('user_login', 'string');

        if (empty($userLogin)) {
            return $this->error(['message' => $this->str->translate('Please enter a username or email address.')], 400);
        }

        $result = $this->userApi->retrievePassword($userLogin);

        if ($this->wpApi->isWpError($result)) {
            return $this->error(['message' => $result->get_error_message()], 404);
        }

        return $this->success(['message' => $this->str->translate('Please check your email for the confirmation link.')]);
    }
}