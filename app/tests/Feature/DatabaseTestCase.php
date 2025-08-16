<?php

namespace Tests\Feature;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class DatabaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        // Vor jedem Test: Transaktion starten (für Isolation)
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Nach jedem Test: Transaktion zurücksetzen
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
        parent::tearDown();
    }
}
