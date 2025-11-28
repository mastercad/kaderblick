<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Entity\UserLevel;
use App\Service\XPService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class XPServiceTest extends TestCase
{
    public function testAddXPToUserCreatesUserLevelAndAddsXP()
    {
        $user = $this->createMock(User::class);
        $userLevel = $this->createMock(UserLevel::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $user->method('getUserLevel')->willReturn(null);
        $user->expects($this->once())->method('setUserLevel');
        $em->expects($this->atLeastOnce())->method('persist');
        $em->expects($this->once())->method('flush');

        $service = new XPService($em);
        $service->addXPToUser($user, 100);
        $this->assertTrue(true); // Wenn keine Exception, ist der Test bestanden
    }

    public function testAddXPToUserAddsXPToExistingUserLevel()
    {
        $user = $this->createMock(User::class);
        $userLevel = $this->createMock(UserLevel::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $user->method('getUserLevel')->willReturn($userLevel);
        $userLevel->method('getXpTotal')->willReturn(50);
        $userLevel->method('getLevel')->willReturn(1);
        $userLevel->expects($this->once())->method('setXpTotal')->with(150);
        $userLevel->expects($this->once())->method('setLevel');
        $userLevel->expects($this->atLeastOnce())->method('setUpdatedAt');
        $em->expects($this->atLeastOnce())->method('persist');
        $em->expects($this->once())->method('flush');

        $service = new XPService($em);
        $service->addXPToUser($user, 100);
        $this->assertTrue(true);
    }
}
