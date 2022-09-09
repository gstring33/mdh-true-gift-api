<?php

namespace App\Services\Mailer;

use App\Services\PHPMailerService;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Psr\Log\LoggerInterface;

class Mailer implements MailerInterface
{
    /** @var PHPMailer */
    private $mailer;
    /** @var string */
    private $from;
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var string */
    private $username;
    /** @var string */
    private $password;
    /** @var int */
    private $debug_mode;
    /** @var LoggerInterface */
    private $logger;

    /**
     * MailerService constructor.
     * @param string $from
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param int $debug_mode
     * @param LoggerInterface $logger
     */
    public function __construct
    (
        string $from,
        string $host,
        string $port,
        string $username,
        string $password,
        int $debug_mode = 0,
        LoggerInterface $logger )
    {
        $this->mailer= new PHPMailer();
        $this->from = $from;
        $this->host = $host;
        $this->port = intval($port);
        $this->username = $username;
        $this->password = $password;
        $this->debug_mode = $debug_mode;
        $this->logger = $logger;
    }

    /**
     * @param array $addresses
     * @param string $subject
     * @param string $body
     * @return void
     */
    public function send(array $addresses, string $subject, string $body)
    {
        $this->initServer($this->host, $this->username, $this->password, $this->port)
            ->initRecipient($this->from, 'Noel Thorigne 2021', $addresses)
            ->initContent($subject, $body);

        try {
            $this->mailer->send();
        }catch (Exception $e) {
            $this->logger->error("Email could not be sent. Subject: {$subject} Mailer Error: {$this->mailer->ErrorInfo}");
        }
    }

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $port
     * @return PHPMailerService
     */
    private function initServer(string $host, string $username, string $password, string $port)
    {
        try {
            if ($this->debug_mode == 1) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            $this->mailer->isSMTP();
            $this->mailer->Host       = $host;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->CharSet    = "UTF-8";
            $this->mailer->Username   = $username;
            $this->mailer->Password   = $password;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = $port;

        } catch (Exception $e) {
            $this->logger->error("Email could not be sent. Subject: Mailer Error: {$this->mailer->ErrorInfo}");
        }

        return $this;
    }

    /**
     * @param string $from
     * @param string $name
     * @param array $adresses
     * @param string|null $replyTo
     * @param string|null $cc
     * @param string|null $bcc
     * @return PHPMailerService
     */
    private function initRecipient(string $from, string $name, array $adresses, ?string $replyTo = null, ?string $cc = null, ?string $bcc =null)
    {
        try {
            $this->mailer->setFrom($from, $name);
            foreach ($adresses as $adress) {
                $this->mailer->addAddress($adress[0], $adress[1]);
            }
            if ($replyTo !== null) {
                $this->mailer->addReplyTo('info@example.com', 'Information');
            }
            if ($cc !== null) {
                $this->mailer->addCC('cc@example.com');
            }
            if ($bcc !== null) {
                $this->mailer->addBCC('bcc@example.com');
            }
        }catch (Exception $e ) {
            $this->logger->error("Email could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
        }

        return $this;
    }

    /**
     * @param array $attachments
     * @return PHPMailerService
     */
    private function initAttachment(array $attachments) {
        try {
            foreach ($attachments as $attachment) {
                $this->mailer->addAttachment($attachment['path'], $attachment['name']);         // Add attachments
            }
        }catch (Exception $e) {
            $this->logger->error("Email could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
        }

        return $this;
    }

    /**
     * @param string $subject
     * @param string $html
     * @return PHPMailerService
     */
    private function initContent(string $subject, string $html)
    {
        try {
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "my-surprise.com - " . $subject;
            $this->mailer->Body    = $html;
            //$this->mailer->AltBody = 'This is the body in plain text for non-HTML mail clients';
        }catch (Exception $e) {
            $this->logger->error("Email could not be sent. Subject: {$subject} Mailer Error: {$this->mailer->ErrorInfo}");
        }

        return $this;
    }
}