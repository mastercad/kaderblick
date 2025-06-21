<?php

namespace App\Repository;

use Symfony\Component\Security\Core\User\UserInterface;

interface OptimizedRepositoryInterface
{
    /**
     * Lädt eine vollständige Liste mit allen Relationen (für Admin-Bereiche)
     */
    public function fetchFullList(?UserInterface $user = null): array;

    /**
     * Lädt eine optimierte Liste mit minimal benötigten Feldern (für Frontend/API)
     */
    public function fetchOptimizedList(?UserInterface $user = null): array;

    /**
     * Lädt einen einzelnen Eintrag mit allen Relationen (für Admin-Detailansicht)
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array;

    /**
     * Lädt einen einzelnen Eintrag mit minimal benötigten Feldern (für Frontend/API)
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array;
}
