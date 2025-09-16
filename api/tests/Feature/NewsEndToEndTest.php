<?php

namespace Tests\Feature;

use Symfony\Component\HttpFoundation\Response;

class NewsEndToEndTest extends ApiWebTestCase
{
    public function testAdminCanCreatePlatformNews(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('POST', '/news/create', [], [], [], json_encode([
            'title' => 'Plattform-News',
            'content' => 'Dies ist eine Plattform-News',
            'visibility' => 'platform',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Allow for success or redirect to success page
        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testAdminCanCreateClubNews(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('POST', '/news/create', [], [], [], json_encode([
            'title' => 'Vereins-News',
            'content' => 'Dies ist eine Vereins-News',
            'visibility' => 'club',
            'club_id' => 1,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testAdminCanCreateTeamNews(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('POST', '/news/create', [], [], [], json_encode([
            'title' => 'Team-News',
            'content' => 'Dies ist eine Team-News',
            'visibility' => 'team',
            'team_id' => 1,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testNonAdminCannotCreateNews(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->authenticateUser($client, 'user6@example.com');

        $client->request('POST', '/news/create');
    }

    public function testUserSeesOnlyRelevantNews(): void
    {
        // Admin legt eine Team-News für Team 1 an
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->authenticateUser($client, 'user16@example.com');

        $crawler = $client->request('POST', '/news/create', [], [], [], json_encode([
            'title' => 'Team 1 News',
            'content' => 'Nur für Team 1',
            'visibility' => 'team',
            'team_id' => 1,
        ]));

        $crawler = $client->request('POST', '/news/create', [], [], [], json_encode([
            'title' => 'Team 2 News',
            'content' => 'Nur für Team 2',
            'visibility' => 'team',
            'team_id' => 2,
        ]));

        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }

        $this->assertTrue($client->getResponse()->isSuccessful());

        // User 1 ist Mutter von Spieler 1 in Team 1 (laut RelationFixtures)
        $this->authenticateUser($client, 'user1@example.com');

        $crawler = $client->request(
            'GET',
            '/news',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $titles = array_column($data['news'], 'title');

        $this->assertContains('Team 1 News', $titles);
        $this->assertNotContains('Team 2 News', $titles);

        // User 2 ist Bruder von Spieler 2 in Team 1, sollte News auch sehen
        $this->authenticateUser($client, 'user2@example.com');

        $crawler = $client->request(
            'GET',
            '/news',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $titles = array_column($data['news'], 'title');

        $this->assertContains('Team 1 News', $titles);
        $this->assertNotContains('Team 2 News', $titles);

        // User 3 ist Vater von Spieler 3 in Team 2 und Mentor von Coach 5, sollte News auch von Team 1 sehen
        $this->authenticateUser($client, 'user3@example.com');

        $crawler = $client->request(
            'GET',
            '/news',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode($response->getContent(), true);

        $titles = array_column($data['news'], 'title');

        $this->assertContains('Team 1 News', $titles);
        $this->assertContains('Team 2 News', $titles);

        // User 2 ist Bruder von Spieler 2 in Team 1, sollte News sehen
        $this->authenticateUser($client, 'user2@example.com');

        $crawler = $client->request(
            'GET',
            '/news',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);
        $titles = array_column($data['news'], 'title');

        $this->assertContains('Team 1 News', $titles);
        $this->assertNotContains('Team 2 News', $titles);
    }

    public function testUserWithoutRelationsCannotCreateNews(): void
    {
        $client = static::createClient();

        $this->authenticateUser($client, 'user6@example.com');

        $client->request('POST', '/news/create', [], [], [], json_encode([
            'title' => 'Team 1 News',
            'content' => 'Nur für Team 1',
            'visibility' => 'team',
            'team_id' => 1,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
