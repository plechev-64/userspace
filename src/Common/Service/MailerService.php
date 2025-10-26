<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Service\Dto\EmailDto;
use UserSpace\Core\TemplateManagerInterface;
use UserSpace\Core\WpApiInterface;

class MailerService implements MailerServiceInterface
{
    public function __construct(
        private readonly TemplateManagerInterface $templateManager,
        private readonly WpApiInterface           $wpApi,
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
}