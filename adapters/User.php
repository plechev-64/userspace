<?php

namespace UserSpace\Adapters;

use UserSpace\Common\Module\User\Src\Domain\UserInterface;

class User implements UserInterface
{
    private \WP_User $wpUser;

    public function __construct(\WP_User $wpUser)
    {
        $this->wpUser = $wpUser;
    }

    public function getId(): int
    {
        return $this->wpUser->ID;
    }

    public function getLogin(): string
    {
        return $this->wpUser->user_login;
    }

    public function getDisplayName(): string
    {
        return $this->wpUser->display_name;
    }

    public function getEmail(): string
    {
        return $this->wpUser->user_email;
    }

    public function getRoles(): array
    {
        return (array) $this->wpUser->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }
}