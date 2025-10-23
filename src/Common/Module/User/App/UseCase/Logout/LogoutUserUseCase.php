<?php

namespace UserSpace\Common\Module\User\App\UseCase\Logout;

use UserSpace\Core\User\UserApiInterface;

class LogoutUserUseCase
{
    public function __construct(
        private readonly UserApiInterface $userApi
    )
    {
    }

    public function execute(): void
    {
        $this->userApi->auth()->logOut();
    }
}