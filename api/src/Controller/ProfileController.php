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
}
