<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRelation;
use App\Service\EmailVerificationService;
use App\Service\UserTitleService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private EmailVerificationService $emailVerificationService,
    ) {
    }

    #[Route('/about-me', name: 'api_about_me', methods: ['GET'])]
    public function getProfile(UserTitleService $userTitleService): JsonResponse
    {
        /** @var ?User $user */
        $user = $this->getUser();

        if (!($user instanceof User)) {
            return $this->json(['message' => 'Not logged in'], 401);
        }

        $isCoach = false;
        $isPlayer = false;
        /** @var UserRelation $userRelation */
        foreach ($user->getUserRelations() as $userRelation) {
            if ('coach' === $userRelation->getRelationType()->getCategory()) {
                $isCoach = true;
            } elseif ('player' === $userRelation->getRelationType()->getCategory()) {
                $isPlayer = true;
            }
            if ($isCoach && $isPlayer) {
                break;
            }
        }

        $titleData = $userTitleService->retrieveTitleDataForUser($user);
        $levelData = $user->getUserLevel() ? [
            'level' => $user->getUserLevel()->getLevel(),
            'xpTotal' => $user->getUserLevel()->getXpTotal()
        ] : null;

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'height' => $user->getHeight(),
            'weight' => $user->getWeight(),
            'shoeSize' => $user->getShoeSize(),
            'shirtSize' => $user->getShirtSize(),
            'pantsSize' => $user->getPantsSize(),
            'roles' => $user->getRoles(),
            'isCoach' => $isCoach,
            'isPlayer' => $isPlayer,
            'avatarFile' => $user->getAvatarFilename(),
            'title' => $titleData,
            'level' => $levelData
        ]);
    }

    #[Route('/update-profile', name: 'api_update_profile', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var ?User $user */
        $user = $this->getUser();

        if (!($user instanceof User)) {
            return $this->json(['message' => 'Not logged in'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $emailChanged = false;

        // Update basic information
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }

        // Handle email change
        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return $this->json(['message' => 'Diese E-Mail-Adresse wird bereits verwendet.'], 400);
            }

            $user->setNewEmail($data['email']);
            $emailChanged = true;
        }

        // Update physical attributes
        if (isset($data['height'])) {
            $user->setHeight((float) $data['height']);
        }
        if (isset($data['weight'])) {
            $user->setWeight((float) $data['weight']);
        }
        if (isset($data['shoeSize'])) {
            $user->setShoeSize((float) $data['shoeSize']);
        }
        if (isset($data['shirtSize'])) {
            $user->setShirtSize($data['shirtSize']);
        }
        if (isset($data['pantsSize'])) {
            $user->setPantsSize($data['pantsSize']);
        }

        // Handle password change
        if (!empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        // Validate user entity
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json(['message' => 'Validierungsfehler', 'errors' => $errorMessages], 400);
        }

        // Save changes
        $this->entityManager->flush();

        // Send verification email if email changed
        if ($emailChanged) {
            $this->emailVerificationService->sendEmailChangeVerification($user);

            return $this->json([
                'message' => 'Profil aktualisiert',
                'emailVerificationRequired' => true,
            ]);
        }

        return $this->json(['message' => 'Profil erfolgreich aktualisiert']);
    }

    #[Route('/verify-email-change/{token}', name: 'api_verify_email_change', methods: ['GET'])]
    public function verifyEmailChange(string $token): JsonResponse
    {
        try {
            $user = $this->emailVerificationService->verifyEmailChangeToken($token);

            if (null === $user) {
                return $this->json(['message' => 'Ungültiger oder abgelaufener Token'], 400);
            }

            $newEmail = $user->getNewEmail();
            $user->setEmail($newEmail);
            $user->setNewEmail(null);

            $this->entityManager->flush();

            return $this->json(['message' => 'E-Mail-Adresse erfolgreich geändert']);
        } catch (Exception $e) {
            return $this->json(['message' => 'Fehler bei der E-Mail-Verifizierung'], 400);
        }
    }

    #[Route('/xp-breakdown', name: 'api_xp_breakdown', methods: ['GET'])]
    public function getXpBreakdown(UserTitleService $userTitleService): JsonResponse
    {
        /** @var ?User $user */
        $user = $this->getUser();
        if (!($user instanceof User)) {
            return $this->json(['message' => 'Not logged in'], 401);
        }

        // XP Breakdown wie bisher
        $xpEventRepo = $this->entityManager->getRepository(\App\Entity\UserXpEvent::class);
        $xpRuleRepo = $this->entityManager->getRepository(\App\Entity\XpRule::class);

        $qb = $xpEventRepo->createQueryBuilder('e')
            ->select('e.actionType, SUM(e.xpValue) as xpSum')
            ->where('e.user = :user')
            ->groupBy('e.actionType')
            ->setParameter('user', $user);
        $xpSums = $qb->getQuery()->getResult();

        $actionTypes = array_column($xpSums, 'actionType');
        $labels = [];
        if ($actionTypes) {
            $rules = $xpRuleRepo->createQueryBuilder('r')
                ->select('r.actionType, r.label')
                ->where('r.actionType IN (:types)')
                ->setParameter('types', $actionTypes)
                ->getQuery()->getResult();
            foreach ($rules as $rule) {
                $labels[$rule['actionType']] = $rule['label'];
            }
        }

        $breakdown = [];
        foreach ($xpSums as $row) {
            $breakdown[] = [
                'actionType' => $row['actionType'],
                'label' => $labels[$row['actionType']] ?? $row['actionType'],
                'xp' => (int) $row['xpSum'],
            ];
        }

        // Titel und Level
        $titleData = $userTitleService->retrieveTitleDataForUser($user);
        $levelData = $user->getUserLevel() ? [
            'level' => $user->getUserLevel()->getLevel(),
            'xpTotal' => $user->getUserLevel()->getXpTotal()
        ] : null;

        return $this->json([
            'breakdown' => $breakdown,
            'title' => $titleData['displayTitle'] ?? null,
            'allTitles' => $titleData['allTitles'] ?? [],
            'level' => $levelData,
            'xpTotal' => $levelData['xpTotal'] ?? null,
        ]);
    }

    #[Route('/admin/title-xp-overview', name: 'admin_title_xp_overview', methods: ['GET'])]
    public function adminTitleXpOverview(EntityManagerInterface $em, UserTitleService $userTitleService): JsonResponse
    {
        // Titel-Übersicht: alle vergebenen Titel mit Nutzeranzahl (über Service)
        $titles = $userTitleService->retrieveTitleStats();

        // Hole alle aktiven PlayerTitles (ohne UserRelation-Join)
        $playerTitleRepo = $em->getRepository(\App\Entity\PlayerTitle::class);
        $activeTitles = $playerTitleRepo->createQueryBuilder('pt')
            ->leftJoin('pt.team', 'team')
            ->leftJoin('pt.player', 'player')
            ->where('pt.isActive = true')
            ->getQuery()->getResult();

        // Gruppiere alle aktiven PlayerTitles nach Titelgruppe und sammle alle Spieler pro Gruppe
        $titlePlayersMap = [];
        foreach ($activeTitles as $pt) {
            $cat = $pt->getTitleCategory();
            $scope = $pt->getTitleScope();
            $rank = $pt->getTitleRank();
            $teamId = $pt->getTeam()?->getId();
            $leagueId = $pt->getLeague()?->getId();
            $key = $cat . '|' . $scope . '|' . $rank . '|' . ($teamId ?? '') . '|' . ($leagueId ?? '');

            $player = $pt->getPlayer();
            if ($player) {
                // Verhindere doppelte Spieler pro Gruppe (z.B. falls mehrere PlayerTitles pro Spieler/Titelgruppe existieren)
                $playerArr = [
                    'id' => $player->getId(),
                    'firstName' => $player->getFirstName(),
                    'lastName' => $player->getLastName(),
                    'email' => $player->getEmail(),
                ];
                $titlePlayersMap[$key][$player->getId()] = $playerArr;
            }
        }

        // Füge Spieler-Liste zu jedem Titel hinzu
        $titlesWithPlayers = array_map(function ($t) use ($titlePlayersMap) {
            $key = $t['titleCategory'] . '|' . $t['titleScope'] . '|' . $t['titleRank'] . '|' . ($t['teamId'] ?? '') . '|' . ($t['leagueId'] ?? '');
            $players = array_values($titlePlayersMap[$key] ?? []);

            return array_merge($t, ['players' => $players]);
        }, $titles);

        // XP-Übersicht: alle Benutzer mit XP, Titel und Level
        $users = $em->createQueryBuilder()
            ->select('u.id, u.firstName, u.lastName, u.email, l.xpTotal AS xp, l.level AS level')
            ->from(User::class, 'u')
            ->leftJoin('u.userLevel', 'l')
            ->orderBy('xp', 'DESC')
            ->getQuery()->getArrayResult();

        return $this->json([
            'titles' => $titlesWithPlayers,
            'users' => $users,
        ]);
    }
}
