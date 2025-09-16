<?php

namespace App\Controller\Api;

use App\Entity\PushSubscription;
use App\Entity\User;
use App\Repository\PushSubscriptionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/push', name: 'api_push_')]
#[IsGranted('ROLE_USER')]
class PushController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PushSubscriptionRepository $pushSubscriptionRepository,
        private ParameterBagInterface $params
    ) {
    }

    /**
     * Get VAPID public key for push subscription.
     */
    #[Route('/vapid-key', name: 'vapid_key', methods: ['GET'])]
    public function getVapidKey(): JsonResponse
    {
        $publicKey = $this->params->get('vapid_public_key');

        if (!$publicKey) {
            return $this->json(['error' => 'VAPID key not configured'], 500);
        }

        return $this->json(['key' => $publicKey]);
    }

    /**
     * Subscribe to push notifications.
     */
    #[Route('/subscribe', name: 'subscribe', methods: ['POST'])]
    public function subscribe(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['subscription'])) {
            return $this->json(['error' => 'Invalid subscription data'], 400);
        }

        $subscriptionData = $data['subscription'];

        // Prüfen ob Subscription bereits existiert
        $existingSubscription = $this->pushSubscriptionRepository->findOneBy([
            'user' => $user,
            'endpoint' => $subscriptionData['endpoint']
        ]);

        if ($existingSubscription) {
            return $this->json(['message' => 'Already subscribed']);
        }

        // Neue Subscription erstellen
        $subscription = new PushSubscription();
        $subscription->setUser($user);
        $subscription->setEndpoint($subscriptionData['endpoint']);
        $subscription->setP256dhKey($subscriptionData['keys']['p256dh'] ?? '');
        $subscription->setAuthKey($subscriptionData['keys']['auth'] ?? '');
        $subscription->setCreatedAt(new DateTime());
        $subscription->setIsActive(true);

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return $this->json(['message' => 'Push subscription created successfully']);
    }

    /**
     * Unsubscribe from push notifications.
     */
    #[Route('/unsubscribe', name: 'unsubscribe', methods: ['POST'])]
    public function unsubscribe(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['subscription']['endpoint'])) {
            return $this->json(['error' => 'Invalid subscription data'], 400);
        }

        $endpoint = $data['subscription']['endpoint'];

        $subscription = $this->pushSubscriptionRepository->findOneBy([
            'user' => $user,
            'endpoint' => $endpoint
        ]);

        if ($subscription) {
            $this->entityManager->remove($subscription);
            $this->entityManager->flush();
        }

        return $this->json(['message' => 'Push subscription removed successfully']);
    }

    /**
     * Get user's push subscriptions.
     */
    #[Route('/subscriptions', name: 'subscriptions', methods: ['GET'])]
    public function getSubscriptions(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $subscriptions = $this->pushSubscriptionRepository->findBy([
            'user' => $user,
            'isActive' => true
        ]);

        return $this->json([
            'subscriptions' => array_map(fn ($s) => [
                'id' => $s->getId(),
                'endpoint' => $s->getEndpoint(),
                'createdAt' => $s->getCreatedAt()->format('Y-m-d H:i:s')
            ], $subscriptions)
        ]);
    }
}
