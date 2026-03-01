<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationRepository $notificationRepository,
        private PushNotificationService $pushNotificationService,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Create a new notification for a user.
     *
     * @param array<mixed> $data
     */
    public function createNotification(
        User $user,
        string $type,
        string $title,
        ?string $message = null,
        ?array $data = null
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user)
            ->setType($type)
            ->setTitle($title)
            ->setMessage($message)
            ->setData($data)
            ->setIsSent(false);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        // Send push notification immediately
        try {
            $url = '/';
            if (is_array($data) && isset($data['url'])) {
                $url = (string) $data['url'];
            }

            $this->pushNotificationService->sendNotification(
                $user,
                $title,
                $message ?? '',
                $url
            );

            $notification->setIsSent(true);
            $this->entityManager->flush();
        } catch (Exception $e) {
            // Log error but don't fail the notification creation
            $this->logger->critical('Failed to send push notification: ' . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Create notification for multiple users.
     *
     * @param User[]       $users
     * @param array<mixed> $data
     *
     * @return Notification[]
     */
    public function createNotificationForUsers(
        array $users,
        string $type,
        string $title,
        ?string $message = null,
        ?array $data = null
    ): array {
        $notifications = [];

        foreach ($users as $user) {
            $notification = new Notification();
            $notification->setUser($user)
                ->setType($type)
                ->setTitle($title)
                ->setMessage($message)
                ->setData($data)
                ->setIsSent(false);

            $this->entityManager->persist($notification);
            $notifications[] = $notification;
        }

        $this->entityManager->flush();

        // Send push notifications for each user
        $url = '/';
        if (is_array($data) && isset($data['url'])) {
            $url = (string) $data['url'];
        }

        foreach ($notifications as $notification) {
            try {
                $this->pushNotificationService->sendNotification(
                    $notification->getUser(),
                    $title,
                    $message ?? '',
                    $url
                );

                $notification->setIsSent(true);
            } catch (Exception $e) {
                $this->logger->warning('Failed to send push for user ' . $notification->getUser()->getId() . ': ' . $e->getMessage());
            }
        }

        $this->entityManager->flush();

        return $notifications;
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): void
    {
        if (!$notification->isRead()) {
            $notification->setIsRead(true);
            $this->entityManager->flush();
        }
    }

    /**
     * Mark multiple notifications as read.
     *
     * @param Notification[] $notifications
     */
    public function markAsReadBatch(array $notifications): void
    {
        foreach ($notifications as $notification) {
            if (!$notification->isRead()) {
                $notification->setIsRead(true);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsReadForUser(User $user): int
    {
        return $this->notificationRepository->markAllAsReadForUser($user);
    }

    /**
     * Get notifications for a user.
     *
     * @return Notification[]
     */
    public function getNotificationsForUser(User $user, int $limit = 50): array
    {
        return $this->notificationRepository->findByUser($user, $limit);
    }

    /**
     * Get unread notifications for a user.
     *
     * @return Notification[]
     */
    public function getUnreadNotificationsForUser(User $user): array
    {
        return $this->notificationRepository->findUnreadByUser($user);
    }

    /**
     * Get unread notification count for a user.
     */
    public function getUnreadCountForUser(User $user): int
    {
        return $this->notificationRepository->countUnreadByUser($user);
    }

    /**
     * Clean up old read notifications.
     */
    public function cleanupOldNotifications(int $daysOld = 30): int
    {
        return $this->notificationRepository->deleteOldReadNotifications($daysOld);
    }

    /**
     * Create a news notification.
     */
    public function createNewsNotification(User $user, string $title, string $message, int $newsId): Notification
    {
        return $this->createNotification(
            $user,
            'news',
            'Neue Nachricht: ' . $title,
            $message,
            ['newsId' => $newsId, 'url' => '/news/' . $newsId]
        );
    }

    /**
     * Create a message notification.
     */
    public function createMessageNotification(User $user, string $sender, string $subject, int $messageId): Notification
    {
        return $this->createNotification(
            $user,
            'message',
            'Neue Nachricht von ' . $sender,
            $subject,
            ['messageId' => $messageId, 'url' => '/messages/' . $messageId]
        );
    }

    /**
     * Create a participation status notification.
     */
    public function createParticipationNotification(
        User $user,
        string $gameName,
        string $status,
        int $participationId
    ): Notification {
        $statusText = match ($status) {
            'confirmed' => 'zugesagt',
            'declined' => 'abgesagt',
            'pending' => 'offen',
            default => $status
        };

        return $this->createNotification(
            $user,
            'participation',
            'Teilnahme ' . $statusText . ': ' . $gameName,
            'Ihre Teilnahme wurde auf "' . $statusText . '" gesetzt.',
            ['participationId' => $participationId, 'url' => '/games/' . $participationId]
        );
    }
}
