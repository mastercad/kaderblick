<?php

namespace App\Service;

use App\Entity\Message;
use App\Entity\RegistrationRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;

class RegistrationNotificationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationService $notificationService,
        private ParameterBagInterface $params,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Notify all admins about a new user registration.
     * Sends a system push notification and a system message with a direct link.
     */
    public function notifyAdminsAboutNewUser(User $newUser): void
    {
        $admins = $this->findAdmins();
        if (empty($admins)) {
            return;
        }

        $userName = trim($newUser->getFirstName() . ' ' . $newUser->getLastName());
        if (!$userName) {
            $userName = $newUser->getEmail();
        }

        $linkPath = '/admin/user-relations?tab=requests';
        $websiteUrl = $this->params->get('app.website_url');
        $fullLink = rtrim((string) $websiteUrl, '/') . $linkPath;

        $subject = 'Neuer Benutzer registriert: ' . $userName;
        $content = sprintf(
            "Der Benutzer <strong>%s</strong> (%s) hat sich soeben registriert.\n\n"
            . "Falls der Benutzer einen Zuordnungsantrag gestellt hat, kannst du ihn hier überprüfen:\n"
            . '<a href="%s">Benutzerverknüpfungen verwalten</a>',
            htmlspecialchars($userName),
            htmlspecialchars($newUser->getEmail()),
            $fullLink
        );

        // System message from the new user to all admins
        $message = new Message();
        $message->setSender($newUser)
            ->setSubject($subject)
            ->setContent($content);

        foreach ($admins as $admin) {
            $message->addRecipient($admin);
        }

        $this->em->persist($message);
        $this->em->flush();

        // Push notifications
        foreach ($admins as $admin) {
            try {
                $this->notificationService->createNotification(
                    $admin,
                    'new_user_registration',
                    $subject,
                    sprintf(
                        '%s hat sich registriert. Klicke hier, um die Zuordnung zu verwalten.',
                        $userName
                    ),
                    ['url' => $linkPath, 'userId' => $newUser->getId()]
                );
            } catch (Throwable $e) {
                $this->logger->error(
                    'Failed to send new-user push notification to admin ' . $admin->getId(),
                    ['error' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * Notify admins about a new registration request (relation request).
     */
    public function notifyAdminsAboutRegistrationRequest(RegistrationRequest $request): void
    {
        $admins = $this->findAdmins();
        if (empty($admins)) {
            return;
        }

        $newUser = $request->getUser();
        $userName = trim($newUser->getFirstName() . ' ' . $newUser->getLastName());
        if (!$userName) {
            $userName = $newUser->getEmail();
        }

        $entityName = $request->getPlayer()?->getFullName() ?? $request->getCoach()?->getFullName() ?? 'unbekannt';
        $relTypeName = $request->getRelationType()->getName();
        $linkPath = '/admin/user-relations?tab=requests';
        $websiteUrl = $this->params->get('app.website_url');
        $fullLink = rtrim((string) $websiteUrl, '/') . $linkPath;

        $subject = sprintf('Neuer Zuordnungsantrag von %s', $userName);
        $content = sprintf(
            '<p>Der Benutzer <strong>%s</strong> (%s) hat einen Zuordnungsantrag gestellt:</p>'
            . '<ul><li><strong>Bezugsperson:</strong> %s</li><li><strong>Beziehung:</strong> %s</li></ul>'
            . '%s'
            . '<p><a href="%s">Antrag jetzt bearbeiten →</a></p>',
            htmlspecialchars($userName),
            htmlspecialchars($newUser->getEmail()),
            htmlspecialchars($entityName),
            htmlspecialchars($relTypeName),
            $request->getNote() ? '<p><strong>Anmerkung:</strong> ' . htmlspecialchars($request->getNote()) . '</p>' : '',
            $fullLink
        );

        $message = new Message();
        $message->setSender($newUser)
            ->setSubject($subject)
            ->setContent($content);

        foreach ($admins as $admin) {
            $message->addRecipient($admin);
        }

        $this->em->persist($message);
        $this->em->flush();

        foreach ($admins as $admin) {
            try {
                $this->notificationService->createNotification(
                    $admin,
                    'registration_request',
                    $subject,
                    sprintf('%s hat einen Zuordnungsantrag gestellt.', $userName),
                    ['url' => $linkPath, 'requestId' => $request->getId()]
                );
            } catch (Throwable $e) {
                $this->logger->error(
                    'Failed to send registration-request push notification to admin ' . $admin->getId(),
                    ['error' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * @return User[]
     */
    private function findAdmins(): array
    {
        return array_filter(
            $this->em->getRepository(User::class)->findAll(),
            fn (User $u) => in_array('ROLE_ADMIN', $u->getRoles(), true)
                || in_array('ROLE_SUPERADMIN', $u->getRoles(), true)
        );
    }
}
