<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetPopulatedProfileForm;

use UserSpace\Common\Module\Form\Src\Domain\Factory\FormFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfigManagerInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;

/**
 * Use Case для получения объекта формы, заполненного данными пользователя.
 */
class GetPopulatedProfileFormUseCase
{
    public function __construct(
        private readonly FormConfigManagerInterface $formConfigManager,
        private readonly FormFactoryInterface       $formFactory,
        private readonly UserApiInterface           $userApi
    )
    {
    }

    public function execute(GetPopulatedProfileFormCommand $command): GetPopulatedProfileFormResult
    {
        $formConfig = $this->formConfigManager->load('profile');
        if (!$formConfig) {
            return new GetPopulatedProfileFormResult(null);
        }

        $form = $this->formFactory->create($formConfig);

        foreach ($form->getFields() as $field) {
            $value = $this->userApi->getUserMeta($command->userId, $field->getName(), true);
            if ($value) {
                $field->setValue($value);
            }
        }

        return new GetPopulatedProfileFormResult($form);
    }
}