<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Service\Dto\EmailDto;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TemplateManagerInterface;
use UserSpace\Core\WpApiInterface;
use WP_User;

class MailerService implements MailerServiceInterface
{
    public function __construct(
        private readonly TemplateManagerInterface $templateManager,
        private readonly WpApiInterface           $wpApi,
        private readonly StringFilterInterface    $str
    )
    {
    }

    public function send(EmailDto $dto): void
    {
        $message = $this->templateManager->render($dto->template, [
            'content' => $dto->content,
            'site_title' => get_bloginfo('name'),
            'subject' => $dto->subject,
        ]);

        // Устанавливаем тип контента для HTML-писем по умолчанию, если не передан другой
        $headers = array_merge(['Content-Type: text/html; charset=UTF-8'], $dto->headers);

        $this->wpApi->mail(
            $dto->to,
            $dto->subject,
            $message,
            $headers,
            $dto->attachments
        );
    }

    public function sendWelcomeEmail(WP_User $user, string $templateName): void
    {
        $subject = $this->str->translate('Welcome to our site!');

        $message = $this->templateManager->render('emails.welcome-email', [
            'user' => $user,
            'templateName' => $templateName,
            'site_title' => get_bloginfo('name'),
        ]);

        // Устанавливаем тип контента для HTML-писем
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        $this->wpApi->mail(
            $user->user_email,
            $subject,
            $message,
            $headers
        );
    }
}