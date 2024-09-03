<?php
namespace App\Service;

use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Message\SlackMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    private NotifierInterface $notifier;
    private MailerInterface $mailer;

    public function __construct(NotifierInterface $notifier, MailerInterface $mailer)
    {
        $this->notifier = $notifier;
        $this->mailer = $mailer;
    }

    public function sendEmail(string $recipient, string $subject, string $body): void
    {
        $email = (new Email())
            ->from('no-reply@ruleengine.com')
            ->to($recipient)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }

    public function sendSlack(string $webhookUrl, string $message): void
    {
        $slackMessage = new SlackMessage();
        $slackMessage->content($message);
        $this->notifier->send($slackMessage, $webhookUrl);
    }
}
