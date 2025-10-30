<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetForgotPasswordForm;

use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;

if (!defined('ABSPATH')) {
    exit;
}

class GetForgotPasswordFormResult
{
    public function __construct(
        public readonly FormInterface $form
    )
    {
    }
}