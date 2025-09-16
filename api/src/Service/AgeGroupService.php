<?php

namespace App\Service;

use App\Entity\AgeGroup;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class AgeGroupService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getAgeGroupByBirthDate(DateTimeInterface $birthDate, ?DateTimeInterface $referenceDate = null): ?AgeGroup
    {
        $referenceDate = $referenceDate ?? new DateTime();
        $age = $birthDate->diff($referenceDate)->y;

        $ageGroups = $this->em->getRepository(AgeGroup::class)->findBy(['active' => true]);

        foreach ($ageGroups as $group) {
            $min = $group->getMinAge();
            $max = $group->getMaxAge();

            if ((null == $min || $age >= $min) && (null == $max || $age <= $max)) {
                return $group;
            }
        }

        return null;
    }
}
