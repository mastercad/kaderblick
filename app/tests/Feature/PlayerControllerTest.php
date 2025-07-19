<?php

namespace App\Tests\Feature;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

final class PlayerControllerTest extends WebTestCase
{
    public function testPlayerList(): void
    {
        $this->markTestIncomplete();
        // @phpstan-ignore-next-line
        $user = $this->createUser();

        $client = static::createClient();
        $client->loginUser($user);

        $client->request('GET', '/api/players');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Player List');
        $this->assertSelectorExists('.player-item');
    }

    public function testPlayerDetail(): void
    {
        $this->markTestIncomplete();
        // @phpstan-ignore-next-line
        $user = $this->createUser();
        $client = static::createClient();

        $client->loginUser($user);
        $client->request('GET', '/api/players/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Player Detail');
        $this->assertSelectorExists('.player-detail');
    }

    public function testPlayerCreate(): void
    {
        $this->markTestIncomplete();
        // @phpstan-ignore-next-line
        $client = static::createClient();
        $client->request('GET', '/api/players/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Create Player');
        $this->assertSelectorExists('form#player-form');
    }

    public function testPlayerUpdate(): void
    {
        $this->markTestIncomplete();
        // @phpstan-ignore-next-line
        $client = static::createClient();
        $client->request('GET', '/api/players/1/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Edit Player');
        $this->assertSelectorExists('form#player-form');
    }

    // @phpstan-ignore-next-line
    private function createUser(int $userId = 1): UserInterface
    {
        $user = new User();
        $user->setFirstName('Andreas');
        $user->setLastName('Kempe');

        return $user;
    }
}
