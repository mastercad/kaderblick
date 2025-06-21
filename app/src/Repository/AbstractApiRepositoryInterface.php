<?php

namespace App\Repository;

use Symfony\Component\Security\Core\User\UserInterface;

interface AbstractApiRepositoryInterface
{
    public function fetchRelevantList(UserInterface $user): array;

    public function fetchRelevantEntry(int $id, UserInterface $user);
}