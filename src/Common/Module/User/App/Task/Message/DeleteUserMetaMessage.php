<?php

namespace UserSpace\Common\Module\User\App\Task\Message;

use UserSpace\Common\Module\Queue\Src\Domain\AbstractMessage;

class DeleteUserMetaMessage extends AbstractMessage
{
    /**
     * @param string[] $metaKeys
     */
    public function __construct(public readonly array $metaKeys)
    {
    }
}