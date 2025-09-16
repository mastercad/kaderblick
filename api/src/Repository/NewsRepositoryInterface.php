<?php

namespace App\Repository;

use App\Entity\News;
use App\Entity\User;

interface NewsRepositoryInterface
{
    /**
     * @return News[]
     */
    public function findForUser(?User $user): array;
}
