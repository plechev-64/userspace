<?php

namespace UserSpace\Common\Module\Locations\App\Default;

use UserSpace\Common\Module\Form\App\UseCase\GetPopulatedProfileForm\GetPopulatedProfileFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\GetPopulatedProfileForm\GetPopulatedProfileFormUseCase;
use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Common\Service\ViewedUserContext;

class ProfileTab extends AbstractTab
{
    public function __construct(
        private readonly GetPopulatedProfileFormUseCase $getPopulatedFormUseCase,
        private readonly ViewedUserContext              $viewedUserContext
    )
    {
        $this->id = 'profile';
        $this->title = __('Profile', 'usp');
        $this->order = 10;
        $this->location = 'sidebar';
        $this->icon = 'dashicons-admin-users';
    }

    public function getContent(): string
    {
        $viewedUser = $this->viewedUserContext->getViewedUser();
        if (!$viewedUser) {
            return '';
        }

        $command = new GetPopulatedProfileFormCommand($viewedUser->getId());
        $result = $this->getPopulatedFormUseCase->execute($command);

        if (!$result->form) {
            return '';
        }

        $output = '<div class="usp-profile-overview">';
        foreach ($result->form->getFields() as $field) {
            $output .= $field->renderValue();
        }
        $output .= '</div>';

        return $output;
    }
}