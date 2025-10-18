<?php

namespace UserSpace\Tabs;

use UserSpace\Core\Tabs\AbstractTab;
use UserSpace\Renderer\GenericFormRenderer;

class EditProfileTab extends AbstractTab
{
    private readonly GenericFormRenderer $genericFormRenderer;

    public function __construct(GenericFormRenderer $genericFormRenderer)
    {
        $this->genericFormRenderer = $genericFormRenderer;

        $this->id = 'edit_profile';
        $this->title = __('Edit Profile', 'usp');
        $this->parentId = 'profile';
        $this->location = 'sidebar';
    }

    public function getContent(): string
    {
        return $this->genericFormRenderer->render(['type' => 'profile', 'action' => '/profile/save']);
    }
}