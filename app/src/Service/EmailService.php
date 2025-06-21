<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $params
    ) {}

    public function sendTemplatedEmail(
        string|array $to,
        string $subject,
        string $template,
        array $context = []
    ): void {
        // Basis-Kontext für alle Emails
        $baseContext = [
            'website_name' => $this->params->get('website_name'),
            'current_year' => date('Y'),
            // Weitere globale Variablen hier...
        ];

        $email = (new TemplatedEmail())
            ->from($this->params->get('mailer_from'))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->textTemplate("emails/$template.txt.twig")
            ->context(array_merge($baseContext, $context));

        $this->mailer->send($email);
    }
}
