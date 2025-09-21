<?php

namespace App\Repository;

use App\Entity\Survey;
use App\Entity\SurveyResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<SurveyResponse>
 */
class SurveyResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyResponse::class);
    }

    /**
     * @param array<int> $surveyIds
     */
    public function deleteBySurvey(Survey $survey): void
    {
        $this->createQueryBuilder('sr')
            ->delete()
            ->where('sr.survey = :survey')
            ->setParameter('survey', $survey)
            ->getQuery()
            ->execute();
    }
}
