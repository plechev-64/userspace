<?php

namespace UserSpace\Common\Module\Form\App\UseCase\GetPopulatedProfileForm;

/**
 * Команда для получения формы, заполненной данными пользователя.
 */
class GetPopulatedProfileFormCommand
{
    public function __construct(
        public readonly int $userId
    )
    {
    }
}