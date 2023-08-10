<?php

namespace App\Message;

class SendEmailNotification
{
    public function __construct(
        private array $emails,
        private string $subject,
        private string $content
    )
    {
    }

    public function getEmails(): array
    {
        return $this->emails;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}