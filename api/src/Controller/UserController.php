<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRelation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users', name: 'api_users_', methods: ['GET'])]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.isEnabled = true')
            ->andWhere('u.isVerified = true')
            ->andWhere('u != :currentUser')
            ->orderBy('u.firstName', 'ASC')
            ->addOrderBy('u.lastName', 'ASC')
            ->setParameter('currentUser', $this->getUser())
            ->getQuery()
            ->getResult();

        return $this->json([
            'users' => array_map(fn (User $user) => [
                'id' => $user->getId(),
                'fullName' => $user->getFullName(),
                'email' => $user->getEmail()
            ], $users)
        ]);
    }

    #[Route('/relations', name: 'relations', methods: ['GET'])]
    public function relations(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json(
            array_map(fn (UserRelation $userRelation) => [
                'id' => $userRelation->getId(),
                'fullName' => $userRelation->getPlayer() ? $userRelation->getPlayer()->getFullname() :
                    ($userRelation->getCoach() ? $userRelation->getCoach()->getFullname() : 'N/A'),
                'identifier' => $userRelation->getRelationType()->getIdentifier(),
                'category' => $userRelation->getRelationType()->getCategory()
            ], $user->getUserRelations()->toArray())
        );
    }
}
