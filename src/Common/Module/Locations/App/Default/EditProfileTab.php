<?php

namespace UserSpace\Common\Module\Locations\App\Default;

use UserSpace\Common\Module\Form\App\UseCase\GetPopulatedProfileForm\GetPopulatedProfileFormCommand;
use UserSpace\Common\Module\Form\App\UseCase\GetPopulatedProfileForm\GetPopulatedProfileFormUseCase;
use UserSpace\Common\Module\Locations\Src\Domain\AbstractTab;
use UserSpace\Common\Service\ViewedUserContext;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\String\StringFilterInterface;

class EditProfileTab extends AbstractTab
{
    public function __construct(
        private readonly GetPopulatedProfileFormUseCase $getPopulatedFormUseCase,
        private readonly ViewedUserContext              $viewedUserContext,
        private readonly StringFilterInterface          $str,
        private readonly AssetRegistryInterface         $assetRegistry,
    )
    {
        $this->id = 'edit_profile';
        $this->title = $str->translate('Edit Profile', 'usp');
        $this->parentId = 'profile';
        $this->location = 'sidebar';
    }

    public function getContent(): string
    {
        $viewedUser = $this->viewedUserContext->getViewedUser();
        if (!$viewedUser) {
            return '';
        }

        $this->assetRegistry->enqueueStyle('usp-form');
        $this->assetRegistry->enqueueScript('usp-form-handler');
        $this->assetRegistry->localizeScript(
            'usp-form-handler',
            'uspL10n',
            [
                'formHandler' => [
                    'saving' => $this->str->translate('Saving...'),
                ],
            ]
        );

        $command = new GetPopulatedProfileFormCommand($viewedUser->getId());
        $result = $this->getPopulatedFormUseCase->execute($command);

        if (!$result->form) {
            return '';
        }

        ob_start();
        echo '<form method="post" class="usp-form" data-usp-form data-usp-action="/form/profile/save">';
        echo $result->form->render();
        echo '<div class="usp-form-submit-wrapper"><button type="submit">' . $this->str->translate('Save') . '</button></div>';
        echo '</form>';

        return ob_get_clean();
    }
}