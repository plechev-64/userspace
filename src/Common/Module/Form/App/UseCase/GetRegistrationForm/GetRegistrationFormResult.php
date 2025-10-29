<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetRegistrationForm;

use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;

if (!defined('ABSPATH')) {
    exit;
}

class GetRegistrationFormResult
{
    public function __construct(
        public readonly FormInterface $form
    )
    {
    }
}