<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    private ?WebPush $webPush = null;

    public function __construct(
        private EntityManagerInterface $em,
        private string $vapidPublicKey,
        private string $vapidPrivateKey
    ) {
    }

    // Für Tests
    public function setWebPush(WebPush $webPush): void
    {
        $this->webPush = $webPush;
    }

    private function getWebPush(): WebPush
    {
        if (!$this->webPush) {
            $this->webPush = new WebPush([
                'VAPID' => [
                    'subject' => 'https://kaderblick.byte-artist.de',
                    'publicKey' => $this->vapidPublicKey,
                    'privateKey' => $this->vapidPrivateKey,
                ],
            ]);
        }

        return $this->webPush;
    }

    public function sendNotification(User $user, string $title, string $body, string $url = '/'): void
    {
        $subscriptions = $user->getPushSubscriptions();
        if ($subscriptions->isEmpty()) {
            return;
        }

        /** @var WebPush $webPush */
        $webPush = $this->getWebPush();

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->getEndpoint(),
                'publicKey' => $sub->getPublicKey(),
                'authToken' => $sub->getAuthToken(),
            ]);

            $webPush->sendOneNotification(
                $subscription,
                json_encode([
                    'title' => $title,
                    'body' => $body,
                    'url' => $url
                ])
            );
        }

        // Handle failed subscriptions
        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                // Remove invalid subscription
                $this->em->remove($report->getSubscription());
            }
        }

        $this->em->flush();
    }
}
