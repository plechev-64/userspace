<?php

namespace UserSpace\Common\Module\Locations\App\Default;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Common\Renderer\GenericFormRenderer;

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
        return $this->genericFormRenderer->render(['type' => 'profile', 'action' => '/form/profile/save']);
    }
}