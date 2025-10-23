<?php

namespace UserSpace\Common\Module\User\App\Task;

use UserSpace\Common\Module\Queue\Src\Domain\MessageHandlerInterface;
use UserSpace\Common\Module\Queue\Src\Domain\MessageInterface;
use UserSpace\Common\Module\User\App\Task\Message\DeleteUserMetaMessage;
use UserSpace\Core\User\UserApiInterface;

class DeleteUserMetaHandler implements MessageHandlerInterface
{
    public function __construct(private readonly UserApiInterface $userApi)
    {
    }

    /**
     * @param DeleteUserMetaMessage $message
     * @return void
     */
    public function handle(MessageInterface $message): void
    {
        if (empty($message->metaKeys)) {
            return;
        }

        foreach ($message->metaKeys as $metaKey) {
            $this->userApi->deleteMetaFromAllUsers($metaKey);
        }
    }
}