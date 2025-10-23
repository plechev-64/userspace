<?php

namespace UserSpace\Common\Module\User\App\UseCase\Avatar;

/**
 * Команда для обновления аватара пользователя.
 */
class UpdateUserAvatarCommand
{
    public function __construct(
        public readonly int $attachmentId,
        public readonly int $userId
    )
    {
    }
}