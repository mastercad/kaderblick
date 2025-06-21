<?php

namespace App\EventSubscriber;

use App\Entity\Player;
use App\Entity\Coach;
use App\Entity\User;
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

        // Prüfen ob bereits ein User mit dieser E-Mail existiert
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $player->getEmail()]);
        if (!$user) {
            return;
        }

        // User mit dem Player/Coach verknüpfen
        if ($player instanceof Player) {
            $user->setClub(null);
            $user->setPlayer($player);
        }

        $entityManager->persist($user);
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

        // User mit dem Player/Coach verknüpfen
        if ($player instanceof Player) {
            $user->setClub(null);
            $user->setPlayer($player);
        }

        $entityManager->persist($user);
        $entityManager->flush();
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

        // User mit dem Player/Coach verknüpfen
        if ($coach instanceof Player) {
            $user->setClub(null);
            $user->setCoach($coach);
        }

        $entityManager->persist($user);
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

        // User mit dem Player/Coach verknüpfen
        if ($coach instanceof Player) {
            $user->setClub(null);
            $user->setCoach($coach);
        }

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
