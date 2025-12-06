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

        $endpointMap = [];

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->getEndpoint(),
                'publicKey' => $sub->getPublicKey(),
                'authToken' => $sub->getAuthToken(),
            ]);

            // Keep a map from endpoint => PushSubscription entity so we can
            // remove the DB entity when the subscription is expired.
            $endpointMap[$subscription->getEndpoint()] = $sub;

            $webPush->sendOneNotification(
                $subscription,
                json_encode([
                    'title' => $title,
                    'body' => $body,
                    'url' => $url,
                    'icon' => 'https://example.com/icon.png',
                    'image' => 'https://example.com/bild.jpg',
                    'badge' => 'https://example.com/badge.png',
                    'vibrate' => [200, 100, 200],
                    'actions' => [
                        [
                            'action' => 'details',
                            'title' => 'Details anzeigen'
                        ]
                    ],
                    'tag' => 'notification-' . $user->getId(),
                    'requireInteraction' => true
                ])
            );
        }

        // Handle failed subscriptions
        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                // If the subscription is expired (HTTP 404/410) remove the
                // corresponding PushSubscription entity. MessageSentReport
                // exposes the endpoint, so map it back to the entity.
                $endpoint = $report->getEndpoint();

                if (isset($endpointMap[$endpoint])) {
                    $this->em->remove($endpointMap[$endpoint]);
                }
            }
        }

        $this->em->flush();
    }
}
