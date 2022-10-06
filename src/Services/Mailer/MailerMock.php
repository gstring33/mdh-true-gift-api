<?php

namespace App\Services\Mailer;

class MailerMock implements MailerInterface
{

    public function send(array $addresses, string $subject, string $body)
    {
        return "Mail successfully sent in dev mode";
    }
}