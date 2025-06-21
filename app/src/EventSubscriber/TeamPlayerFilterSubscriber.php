<?php

namespace App\EventSubscriber;

use App\Entity\Player;
use App\Entity\Team;
use DateTimeImmutable;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs as EventLifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class TeamPlayerFilterSubscriber implements EventSubscriber
{
    public function __construct(private Security $security) {}
    
    public function getSubscribedEvents(): array
    {
        return [Events::postLoad];
    }

    public function postLoad(EventLifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Team) {
            return;
        }

        /** @var Coach $currentCoach */
        $currentCoach = $this->security->getUser();

        $now = new DateTimeImmutable();

        $filteredPlayers = $entity->getPlayers()->filter(function (Player $player) use ($currentCoach, $now) {
            /** @var Team $team */
            foreach ($player->getTeams() as $team) {
                if (
                    in_array($currentCoach, $team->getCoaches())
                    && $team->getStartDate() <= $now &&
                    ($team->getEndDate() === null || $team->getEndDate() >= $now)
                ) {
                    return true;
                }
            }
            return false;
        });

        $entity->setPlayers($filteredPlayers);
    }
}
