<?php

namespace UserSpace\Common\Module\User\App\Task;

use UserSpace\Common\Module\Queue\Src\Domain\MessageHandlerInterface;
use UserSpace\Common\Module\Queue\Src\Domain\MessageInterface;
use UserSpace\Common\Module\User\App\Task\Message\SendWelcomeEmailMessage;
use UserSpace\Common\Service\Dto\EmailDto;
use UserSpace\Common\Service\MailerServiceInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\User\UserApiInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class SendWelcomeEmailHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly UserApiInterface       $userApi,
        private readonly MailerServiceInterface $mailerService,
        private readonly StringFilterInterface  $str
    )
    {
    }

    /**
     * @param SendWelcomeEmailMessage $message
     */
    public function handle(MessageInterface $message): void
    {
        if (!$message instanceof SendWelcomeEmailMessage) {
            return;
        }

        $user = $this->userApi->getUserdata($message->userId);

        if ($user) {

            $subject = $this->str->translate('Welcome to our site!');
            $content = sprintf(
                '<p>%s</p><p>%s</p><p>%s</p>',
                sprintf($this->str->translate('Hello, %s!'), $user->display_name),
                $this->str->translate('Thank you for registering on our site.'),
                sprintf($this->str->translate('You chose the template: %s.'), '<strong>' . $this->str->escHtml($message->templateName) . '</strong>')
            );

            $emailDto = new EmailDto(
                $user->user_email,
                $subject,
                $content
            );

            $this->mailerService->send($emailDto);
        }
    }
}