<?php

namespace UserSpace\Common\Module\User\App\Task;

use UserSpace\Common\Module\Queue\Src\Domain\MessageHandlerInterface;
use UserSpace\Common\Module\Queue\Src\Domain\MessageInterface;
use UserSpace\Common\Module\User\App\Task\Message\SendConfirmationEmailMessage;
use UserSpace\Common\Service\Dto\EmailDto;
use UserSpace\Common\Service\MailerServiceInterface;

if (!defined('ABSPATH')) {
    exit;
}

class SendConfirmationEmailHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly MailerServiceInterface $mailerService
    )
    {
    }

    /**
     * @param SendConfirmationEmailMessage $message
     * @return void
     */
    public function handle(MessageInterface $message): void
    {
        if (!$message instanceof SendConfirmationEmailMessage) {
            return;
        }

        $emailDto = new EmailDto(
            $message->to,
            $message->subject,
            $message->body
        );

        $this->mailerService->send($emailDto);
    }
}