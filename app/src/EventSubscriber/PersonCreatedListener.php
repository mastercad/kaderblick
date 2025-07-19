<?php

namespace App\EventSubscriber;

use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\RelationType;
use App\Entity\User;
use App\Entity\UserRelation;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersistPlayer', entity: Player::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersistCoach', entity: Coach::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdatePlayer', entity: Player::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdateCoach', entity: Coach::class)]
class PersonCreatedListener
{
    public function postUpdatePlayer(Player $player, PostUpdateEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();

        // Nur fortfahren wenn eine E-Mail gesetzt ist
        if (empty($player->getEmail())) {
            return;
        }

        // Prüfen ob ein User mit dieser E-Mail existiert
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $player->getEmail()]);

        if (!$user) {
            return;
        }

        // Prüfen, ob bereits eine relation besteht:
        $userPlayerRelation = $entityManager->getRepository(UserRelation::class)->findOneBy(['user' => $user, 'player' => $player]);
        if ($userPlayerRelation instanceof UserRelation && 'self_player' === $userPlayerRelation->getRelationType()->getIdentifier()) {
            return;
        }

        $userPlayerRelation = new UserRelation();
        $userPlayerRelationType = $entityManager->getRepository(RelationType::class)->findOneBy(['identifier' => 'self_player']);
        $userPlayerRelation->setRelationType($userPlayerRelationType);
        $userPlayerRelation->setRelatedUser($user);
        $userPlayerRelation->setPlayer($player);

        $entityManager->persist($userPlayerRelation);
        $entityManager->flush();
    }

    public function postPersistPlayer(Player $player, PostPersistEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();

        // Nur fortfahren wenn eine E-Mail gesetzt ist
        if (empty($player->getEmail())) {
            return;
        }

        // Prüfen ob bereits ein User mit dieser E-Mail existiert
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $player->getEmail()]);

        if (!$user) {
            return;
        }

        $userPlayerSelfRelationType = $entityManager->getRepository(RelationType::class)->findOneBy(['identifier' => 'self_player']);

        $userRelation = new UserRelation();
        $userRelation->setPlayer($player);
        $userRelation->setRelatedUser($user);
        $userRelation->setRelationType($userPlayerSelfRelationType);

        $entityManager->persist($userRelation);
    }

    public function postPersistCoach(Coach $coach, PostPersistEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();

        // Nur fortfahren wenn eine E-Mail gesetzt ist
        if (empty($coach->getEmail())) {
            return;
        }

        // Prüfen ob bereits ein User mit dieser E-Mail existiert
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $coach->getEmail()]);

        if (!$user) {
            return;
        }

        $userCoachSelfRelationType = $entityManager->getRepository(RelationType::class)->findOneBy(['identifier' => 'self_coach']);

        $userRelation = new UserRelation();
        $userRelation->setCoach($coach);
        $userRelation->setRelatedUser($user);
        $userRelation->setRelationType($userCoachSelfRelationType);

        $entityManager->persist($userRelation);
        $entityManager->flush();
    }

    public function postUpdateCoach(Coach $coach, PostUpdateEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();

        // Nur fortfahren wenn eine E-Mail gesetzt ist
        if (empty($coach->getEmail())) {
            return;
        }

        // Prüfen ob bereits ein User mit dieser E-Mail existiert
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $coach->getEmail()]);

        if (!$user) {
            return;
        }

        // Prüfen, ob bereits eine relation besteht:
        $userCoachRelation = $entityManager->getRepository(UserRelation::class)->findOneBy(['user' => $user, 'coach' => $coach]);
        if ($userCoachRelation instanceof UserRelation && 'self_coach' === $userCoachRelation->getRelationType()->getIdentifier()) {
            return;
        }

        $userCoachRelation = new UserRelation();
        $userCoachRelationType = $entityManager->getRepository(RelationType::class)->findOneBy(['identifier' => 'self_coach']);
        $userCoachRelation->setRelationType($userCoachRelationType);
        $userCoachRelation->setRelatedUser($user);
        $userCoachRelation->setCoach($coach);

        $entityManager->persist($userCoachRelation);
        $entityManager->flush();
    }
}
