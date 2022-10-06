<?php

namespace App\Services\Mailer;

interface MailerInterface
{
    public function send(array $addresses, string $subject, string $body);
}