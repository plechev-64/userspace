<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetPopulatedProfileForm;

use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;

/**
 * Результат выполнения GetPopulatedFormUseCase.
 */
class GetPopulatedProfileFormResult
{
    public function __construct(
        public readonly ?FormInterface $form
    )
    {
    }
}