<?php

namespace UserSpace\Tabs;

use UserSpace\Core\Tabs\AbstractTab;
use UserSpace\Service\ShortcodeManager;

class EditProfileTab extends AbstractTab
{
    private ShortcodeManager $shortcodeManager;

    public function __construct(ShortcodeManager $shortcodeManager)
    {
        $this->shortcodeManager = $shortcodeManager;

        $this->id = 'edit_profile';
        $this->title = __('Edit Profile', 'usp');
        $this->parentId = 'profile';
        $this->location = 'sidebar';
    }

    public function getContent(): string
    {
        return $this->shortcodeManager->renderProfileFormForAccount();
    }
}