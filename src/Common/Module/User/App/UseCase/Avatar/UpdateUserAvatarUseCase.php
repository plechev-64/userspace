<?php

namespace UserSpace\Common\Module\User\App\UseCase\Avatar;

use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Media\MediaApiInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\User\UserApiInterface;

class UpdateUserAvatarUseCase
{
    public const AVATAR_META_KEY = 'usp_avatar_id';

    public function __construct(
        private readonly UserApiInterface      $userApi,
        private readonly MediaApiInterface     $mediaApi,
        private readonly StringFilterInterface $str
    ) {
    }

    /**
     * @throws UspException
     */
    public function execute(UpdateUserAvatarCommand $command): void
    {
        if ($command->userId === 0) {
            throw new UspException($this->str->translate('You must be logged in to update your avatar.'), 403);
        }

        if ($command->attachmentId === 0) {
            throw new UspException($this->str->translate('Attachment ID is missing.'), 400);
        }

        // Проверяем, что файл является изображением
        if (!$this->mediaApi->isAttachmentImage($command->attachmentId)) {
            throw new UspException($this->str->translate('The uploaded file is not an image.'), 400);
        }

        // Сохраняем ID аватара в мета-поле пользователя
        $this->userApi->updateUserMeta(
            $command->userId,
            self::AVATAR_META_KEY,
            $command->attachmentId
        );
    }
}