<?php

namespace Tests\Feature\Controller;

use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\RegistrationRequest;
use App\Entity\RelationType;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\ApiWebTestCase;

class RegistrationRequestControllerTest extends ApiWebTestCase
{
    // ────────────────────────────── GET /api/registration-request/context ──────────────────────────────

    public function testContextIsPubliclyAccessible(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/registration-request/context');

        $this->assertResponseIsSuccessful();
    }

    public function testContextReturnsPlayersCoachesAndRelationTypes(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/registration-request/context');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('players', $data);
        $this->assertArrayHasKey('coaches', $data);
        $this->assertArrayHasKey('relationTypes', $data);
        $this->assertIsArray($data['players']);
        $this->assertIsArray($data['coaches']);
        $this->assertIsArray($data['relationTypes']);
    }

    public function testContextPlayerItemHasExpectedFields(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/registration-request/context');

        $data = json_decode($client->getResponse()->getContent(), true);

        if (!empty($data['players'])) {
            $player = $data['players'][0];
            $this->assertArrayHasKey('id', $player);
            $this->assertArrayHasKey('fullName', $player);
        } else {
            $this->markTestSkipped('No players in test database.');
        }
    }

    public function testContextRelationTypeItemHasExpectedFields(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/registration-request/context');

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertNotEmpty($data['relationTypes'], 'Relation types should not be empty.');
        $rt = $data['relationTypes'][0];
        $this->assertArrayHasKey('id', $rt);
        $this->assertArrayHasKey('identifier', $rt);
        $this->assertArrayHasKey('name', $rt);
        $this->assertArrayHasKey('category', $rt);
    }

    // ────────────────────────────── POST /api/registration-request ──────────────────────────────

    public function testSubmitRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], '{}');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testSubmitRejectsMissingFields(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testSubmitRejectsInvalidEntityType(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $em = static::getContainer()->get('doctrine')->getManager();
        $relationType = $em->getRepository(RelationType::class)->findOneBy([]);
        $this->assertNotNull($relationType, 'No RelationType found in test fixtures.');

        $payload = json_encode([
            'entityType' => 'invalid_type',
            'entityId' => 1,
            'relationTypeId' => $relationType->getId(),
        ]);

        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testSubmitWithPlayerCreatesRequest(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $em = static::getContainer()->get('doctrine')->getManager();

        $player = $em->getRepository(Player::class)->findOneBy([]);
        $this->assertNotNull($player, 'No Player found in test fixtures.');

        $relationType = $em->getRepository(RelationType::class)->findOneBy(['category' => 'player']);
        $this->assertNotNull($relationType, 'No player RelationType found in test fixtures.');

        // Remove any existing pending requests for this user first
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user6@example.com']);
        $existing = $em->getRepository(RegistrationRequest::class)->findBy(['user' => $user, 'status' => 'pending']);
        foreach ($existing as $r) {
            $em->remove($r);
        }
        $em->flush();

        $payload = json_encode([
            'entityType' => 'player',
            'entityId' => $player->getId(),
            'relationTypeId' => $relationType->getId(),
            'note' => 'Test note',
        ]);

        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }

    public function testSubmitWithCoachCreatesRequest(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $em = static::getContainer()->get('doctrine')->getManager();

        $coach = $em->getRepository(Coach::class)->findOneBy([]);
        $this->assertNotNull($coach, 'No Coach found in test fixtures.');

        $relationType = $em->getRepository(RelationType::class)->findOneBy(['category' => 'coach']);
        $this->assertNotNull($relationType, 'No coach RelationType found in test fixtures.');

        // Remove any existing pending requests for this user first
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user6@example.com']);
        $existing = $em->getRepository(RegistrationRequest::class)->findBy(['user' => $user, 'status' => 'pending']);
        foreach ($existing as $r) {
            $em->remove($r);
        }
        $em->flush();

        $payload = json_encode([
            'entityType' => 'coach',
            'entityId' => $coach->getId(),
            'relationTypeId' => $relationType->getId(),
        ]);

        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testSubmitReturnsBadRequestForUnknownRelationType(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $em = static::getContainer()->get('doctrine')->getManager();
        $player = $em->getRepository(Player::class)->findOneBy([]);
        $this->assertNotNull($player);

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user6@example.com']);
        $existing = $em->getRepository(RegistrationRequest::class)->findBy(['user' => $user, 'status' => 'pending']);
        foreach ($existing as $r) {
            $em->remove($r);
        }
        $em->flush();

        $payload = json_encode([
            'entityType' => 'player',
            'entityId' => $player->getId(),
            'relationTypeId' => 99999,
        ]);

        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testSubmitReturnsBadRequestForUnknownPlayer(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $em = static::getContainer()->get('doctrine')->getManager();
        $relationType = $em->getRepository(RelationType::class)->findOneBy(['category' => 'player']);
        $this->assertNotNull($relationType);

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user6@example.com']);
        $existing = $em->getRepository(RegistrationRequest::class)->findBy(['user' => $user, 'status' => 'pending']);
        foreach ($existing as $r) {
            $em->remove($r);
        }
        $em->flush();

        $payload = json_encode([
            'entityType' => 'player',
            'entityId' => 99999,
            'relationTypeId' => $relationType->getId(),
        ]);

        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testSubmitReturnsBadRequestForUnknownCoach(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $em = static::getContainer()->get('doctrine')->getManager();
        $relationType = $em->getRepository(RelationType::class)->findOneBy(['category' => 'coach']);
        $this->assertNotNull($relationType);

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user6@example.com']);
        $existing = $em->getRepository(RegistrationRequest::class)->findBy(['user' => $user, 'status' => 'pending']);
        foreach ($existing as $r) {
            $em->remove($r);
        }
        $em->flush();

        $payload = json_encode([
            'entityType' => 'coach',
            'entityId' => 99999,
            'relationTypeId' => $relationType->getId(),
        ]);

        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testSubmitRejectsDuplicatePendingRequest(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $em = static::getContainer()->get('doctrine')->getManager();

        $player = $em->getRepository(Player::class)->findOneBy([]);
        $this->assertNotNull($player);

        $relationType = $em->getRepository(RelationType::class)->findOneBy(['category' => 'player']);
        $this->assertNotNull($relationType);

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user6@example.com']);
        $existing = $em->getRepository(RegistrationRequest::class)->findBy(['user' => $user, 'status' => 'pending']);
        foreach ($existing as $r) {
            $em->remove($r);
        }
        $em->flush();

        $payload = json_encode([
            'entityType' => 'player',
            'entityId' => $player->getId(),
            'relationTypeId' => $relationType->getId(),
        ]);

        // First request
        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Second request — should return 409 Conflict
        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    // ────────────────────────────── GET /api/registration-request/mine ──────────────────────────────

    public function testMineRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/registration-request/mine');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testMineReturnsNullWhenNoPendingRequest(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        // Ensure no pending requests exist
        $em = static::getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user6@example.com']);
        $pending = $em->getRepository(RegistrationRequest::class)->findBy(['user' => $user, 'status' => 'pending']);
        foreach ($pending as $r) {
            $em->remove($r);
        }
        $em->flush();

        $client->request('GET', '/api/registration-request/mine');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('request', $data);
        $this->assertNull($data['request']);
    }

    public function testMineReturnsPendingRequest(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $em = static::getContainer()->get('doctrine')->getManager();

        $player = $em->getRepository(Player::class)->findOneBy([]);
        $this->assertNotNull($player);

        $relationType = $em->getRepository(RelationType::class)->findOneBy(['category' => 'player']);
        $this->assertNotNull($relationType);

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user6@example.com']);
        $existing = $em->getRepository(RegistrationRequest::class)->findBy(['user' => $user, 'status' => 'pending']);
        foreach ($existing as $r) {
            $em->remove($r);
        }
        $em->flush();

        // Create a pending request
        $payload = json_encode([
            'entityType' => 'player',
            'entityId' => $player->getId(),
            'relationTypeId' => $relationType->getId(),
        ]);
        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Fetch own pending request
        $client->request('GET', '/api/registration-request/mine');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('request', $data);
        $this->assertNotNull($data['request']);
        $this->assertEquals('pending', $data['request']['status']);
        // API returns separate player/coach fields, not a unified entityType
        $this->assertNotNull($data['request']['player']);
        $this->assertNull($data['request']['coach']);
    }

    public function testMineReturnsExpectedFields(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com');

        $em = static::getContainer()->get('doctrine')->getManager();

        $player = $em->getRepository(Player::class)->findOneBy([]);
        $this->assertNotNull($player);

        $relationType = $em->getRepository(RelationType::class)->findOneBy(['category' => 'player']);
        $this->assertNotNull($relationType);

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user6@example.com']);
        $existing = $em->getRepository(RegistrationRequest::class)->findBy(['user' => $user, 'status' => 'pending']);
        foreach ($existing as $r) {
            $em->remove($r);
        }
        $em->flush();

        $payload = json_encode([
            'entityType' => 'player',
            'entityId' => $player->getId(),
            'relationTypeId' => $relationType->getId(),
            'note' => 'Test Anmerkung',
        ]);
        $client->request('POST', '/api/registration-request', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $client->request('GET', '/api/registration-request/mine');

        $data = json_decode($client->getResponse()->getContent(), true);
        $req = $data['request'];

        $this->assertArrayHasKey('id', $req);
        $this->assertArrayHasKey('status', $req);
        $this->assertArrayHasKey('player', $req);
        $this->assertArrayHasKey('coach', $req);
        $this->assertArrayHasKey('relationType', $req);
        $this->assertArrayHasKey('createdAt', $req);
        $this->assertNotNull($req['player']);
        $this->assertNull($req['coach']);
        $this->assertEquals('Test Anmerkung', $req['note']);
    }
}
