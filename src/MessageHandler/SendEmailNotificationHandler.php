<?php

namespace App\MessageHandler;

use App\Message\SendEmailNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class SendEmailNotificationHandler
{
    public function __construct(
        private MailerInterface $mailer
    )
    {
    }

    public function __invoke(SendEmailNotification $sendEmailNotification): void
    {
        $email = (new Email())
            ->from('mailer@listing-app.com')
            ->bcc(...$sendEmailNotification->getEmails())
            ->subject('New listing to verify!')
            ->html($sendEmailNotification->getContent());

        $this->mailer->send($email);
    }
}