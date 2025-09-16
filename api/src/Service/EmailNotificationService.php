<?php

namespace App\Service;

use App\Entity\CalendarEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailNotificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
    ) {
    }

    /**
     * @param array<int, string> $recipients
     * @param array<int, string> $uploadedFiles
     */
    public function sendUploadNotification(array $recipients, string $folderUrl, array $uploadedFiles): void
    {
        $email = (new Email())
            ->from($_ENV['MAILER_FROM'])
            ->subject('Neue Videos wurden hochgeladen');

        foreach ($recipients as $recipient) {
            $email->addTo($recipient);
        }

        $html = $this->twig->render('emails/video_upload.html.twig', [
            'folderUrl' => $folderUrl,
            'files' => $uploadedFiles
        ]);

        $email->html($html);
        $this->mailer->send($email);
    }

    /**
     * @param array<int, string> $recipients
     */
    public function sendEventNotification(array $recipients, CalendarEvent $calendarEvent): void
    {
        $email = (new Email())
            ->from($_ENV['MAILER_FROM'])
            ->subject('Neuer Termin: ' . $calendarEvent->getTitle());

        foreach ($recipients as $recipient) {
            $email->addTo($recipient);
        }

        $html = $this->twig->render('emails/event_notification.html.twig', [
            'event' => $calendarEvent
        ]);

        $text = $this->twig->render('emails/event_notification.txt.twig', [
            'event' => $calendarEvent
        ]);

        $email
            ->html($html)
            ->text($text);

        $this->mailer->send($email);
    }
}
