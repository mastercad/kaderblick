<?php

namespace App\Tests\Unit\Controller;

use App\Controller\Api\GamesController;
use App\Service\VideoTimelineService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class GamesControllerTest extends TestCase
{
    /**
     * Test dass der Controller korrekt initialisiert werden kann mit VideoTimelineService.
     */
    public function testControllerInitialization(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $videoTimelineService = $this->createMock(VideoTimelineService::class);

        $controller = new GamesController($entityManager, $videoTimelineService);

        $this->assertInstanceOf(GamesController::class, $controller);
    }
}
