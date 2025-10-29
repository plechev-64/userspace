<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetLoginForm;

use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;

if (!defined('ABSPATH')) {
    exit;
}

class GetLoginFormResult
{
    public function __construct(
        public readonly FormInterface $form
    )
    {
    }
}