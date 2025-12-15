<?php

namespace App\Controller\Api;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Security\Voter\NotificationVoter;
use App\Service\NotificationService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notifications', name: 'api_notifications_')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService,
        private NotificationRepository $notificationRepository
    ) {
    }

    /**
     * Get all notifications for the current user.
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $limit = min((int) $request->query->get('limit', 50), 100);

        $notifications = $this->notificationService->getNotificationsForUser($user, $limit);

        // Filtere basierend auf VIEW-Berechtigung
        $notifications = array_filter($notifications, fn ($n) => $this->isGranted(NotificationVoter::VIEW, $n));

        return $this->json([
            'notifications' => array_map(fn ($n) => $n->toArray(), $notifications),
            'unreadCount' => $this->notificationService->getUnreadCountForUser($user)
        ]);
    }

    /**
     * Get unread notifications for the current user.
     */
    #[Route('/unread', name: 'unread', methods: ['GET'])]
    public function unread(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $notifications = $this->notificationService->getUnreadNotificationsForUser($user);

        return $this->json([
            'notifications' => array_map(fn ($n) => $n->toArray(), $notifications),
            'count' => count($notifications)
        ]);
    }

    /**
     * Mark a notification as read.
     */
    #[Route('/{id}/read', name: 'mark_read', methods: ['POST'])]
    public function markRead(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $notification = $this->notificationRepository
            ->findOneBy(['id' => $id, 'user' => $user]);

        if (!$notification) {
            return $this->json(['error' => 'Notification not found'], 404);
        }

        $this->notificationService->markAsRead($notification);

        return $this->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for the current user.
     */
    #[Route('/read-all', name: 'mark_all_read', methods: ['POST'])]
    public function markAllRead(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $count = $this->notificationService->markAllAsReadForUser($user);

        return $this->json(['markedCount' => $count]);
    }

    /**
     * Get notification statistics for the current user.
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $unreadCount = $this->notificationService->getUnreadCountForUser($user);
        $allNotifications = $this->notificationService->getNotificationsForUser($user, 100);

        $typeStats = [];
        foreach ($allNotifications as $notification) {
            $type = $notification->getType();
            if (!isset($typeStats[$type])) {
                $typeStats[$type] = ['total' => 0, 'unread' => 0];
            }
            ++$typeStats[$type]['total'];
            if (!$notification->isRead()) {
                ++$typeStats[$type]['unread'];
            }
        }

        return $this->json([
            'totalUnread' => $unreadCount,
            'totalNotifications' => count($allNotifications),
            'byType' => $typeStats
        ]);
    }

    /**
     * Create a test notification (for development/testing).
     */
    #[Route('/test', name: 'test', methods: ['POST'])]
    public function createTestNotification(Request $request, LoggerInterface $logger): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $type = $data['type'] ?? 'system';
        $title = $data['title'] ?? 'Test Notification';
        $message = $data['message'] ?? 'Dies ist eine Test-Benachrichtigung';

        try {
            $notification = $this->notificationService->createNotification(
                $user,
                $type,
                $title,
                $message,
                ['test' => true, 'timestamp' => time()]
            );

            return $this->json([
                'success' => true,
                'notification' => $notification->toArray(),
                'message' => 'Test-Benachrichtigung erfolgreich erstellt'
            ]);
        } catch (Exception $e) {
            $logger->critical('Error creating test notification: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => 'Fehler beim Erstellen der Test-Benachrichtigung: ' . $e->getMessage()
            ], 500);
        }
    }
}
