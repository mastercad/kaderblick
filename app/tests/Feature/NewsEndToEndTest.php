<?php

namespace Tests\Feature;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class NewsEndToEndTest extends ApiWebTestCase
{
    public function testAdminCanCreatePlatformNews(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->authenticateUser($client, 'user16@example.com');

        $crawler = $client->request('GET', '/news/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Absenden')->form([
            'title' => 'Plattform-News',
            'content' => 'Dies ist eine Plattform-News',
            'visibility' => 'platform',
        ]);

        $client->submit($form);

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

        $crawler = $client->request('GET', '/news/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Club 1 existiert laut Fixtures
        $form = $crawler->selectButton('Absenden')->form([
            'title' => 'Vereins-News',
            'content' => 'Dies ist eine Vereins-News',
            'visibility' => 'club',
            'club_id' => 1,
        ]);
        $client->submit($form);

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

        $crawler = $client->request('GET', '/news/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Team 1 existiert laut Fixtures
        $form = $crawler->selectButton('Absenden')->form([
            'title' => 'Team-News',
            'content' => 'Dies ist eine Team-News',
            'visibility' => 'team',
            'team_id' => 1,
        ]);
        $client->submit($form);

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

        $client->request('GET', '/news/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUserSeesOnlyRelevantNews(): void
    {
        // Admin legt eine Team-News für Team 1 an
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->authenticateUser($client, 'user16@example.com');

        $crawler = $client->request('GET', '/news/create');
        $form = $crawler->selectButton('Absenden')->form([
            'title' => 'Team 1 News',
            'content' => 'Nur für Team 1',
            'visibility' => 'team',
            'team_id' => 1,
        ]);
        $client->submit($form);

        $crawler = $client->request('GET', '/news/create');
        $form = $crawler->selectButton('Absenden')->form([
            'title' => 'Team 2 News',
            'content' => 'Nur für Team 2',
            'visibility' => 'team',
            'team_id' => 2,
        ]);
        $client->submit($form);

        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }

        $this->assertTrue($client->getResponse()->isSuccessful());

        // User 1 ist Mutter von Spieler 1 in Team 1 (laut RelationFixtures)
        $this->authenticateUser($client, 'user1@example.com');

        $crawler = $client->request('GET', '/news');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Prüfe auf den eindeutigen Content "Nur für Team 1"
        $content = $crawler->html();
        $this->assertStringContainsString('Nur für Team 1', $content);
        $this->assertStringNotContainsString('Nur für Team 2', $content);

        // User 2 ist Bruder von Spieler 2 in Team 1, sollte News auch sehen
        $this->authenticateUser($client, 'user2@example.com');

        $crawler = $client->request('GET', '/news');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $content = $crawler->html();
        $this->assertStringContainsString('Nur für Team 1', $content);
        $this->assertStringNotContainsString('Nur für Team 2', $content);

        // User 3 ist Vater von Spieler 3 in Team 2 und Mentor von Coach 5, sollte News auch von Team 1 sehen
        $this->authenticateUser($client, 'user3@example.com');

        $crawler = $client->request('GET', '/news');
        $content = $crawler->html();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertStringContainsString('Nur für Team 1', $content);
        $this->assertStringContainsString('Nur für Team 2', $content);

        // User 2 ist Bruder von Spieler 2 in Team 1, sollte News sehen
        $this->authenticateUser($client, 'user2@example.com');

        $crawler = $client->request('GET', '/news');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $content = $crawler->html();
        $this->assertStringContainsString('Nur für Team 1', $content);
        $this->assertStringContainsString('Nur für Team 1', $content);
    }

    public function testUserWithRelationCanCreateNews(): void
    {
        // User 16 ist Admin, darf News anlegen
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->authenticateUser($client, 'user16@example.com');

        $crawler = $client->request('GET', '/news/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // User 6 ist normaler User, darf KEINE News anlegen
        $this->authenticateUser($client, 'user6@example.com');

        $client->request('GET', '/news/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
