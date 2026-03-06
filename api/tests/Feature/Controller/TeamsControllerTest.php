<?php

namespace Tests\Feature\Controller;

use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\ApiWebTestCase;

class TeamsControllerTest extends ApiWebTestCase
{
    // ────────────────────────────── Paginated Index ──────────────────────────────

    public function testIndexReturnsPaginatedStructure(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com'); // ROLE_ADMIN

        $client->request('GET', '/api/teams');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('teams', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertIsArray($data['teams']);
        $this->assertIsInt($data['total']);
        $this->assertEquals(1, $data['page']);
        $this->assertEquals(25, $data['limit']);
    }

    public function testIndexRespectsCustomPageAndLimit(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams?page=1&limit=5');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(1, $data['page']);
        $this->assertEquals(5, $data['limit']);
        $this->assertLessThanOrEqual(5, count($data['teams']));
    }

    public function testIndexLimitIsCappedAt100(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams?limit=500');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(100, $data['limit']);
    }

    public function testIndexPageMinimumIs1(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams?page=0');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(1, $data['page']);
    }

    public function testIndexPaginationReturnsConsistentTotal(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams?page=1&limit=3');
        $data1 = json_decode($client->getResponse()->getContent(), true);
        $total = $data1['total'];

        $client->request('GET', '/api/teams?page=2&limit=3');
        $data2 = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($total, $data2['total']);

        if ($total > 3) {
            $this->assertNotEmpty($data2['teams']);
        }
    }

    public function testIndexBeyondLastPageReturnsEmpty(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams?page=99999&limit=25');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertEmpty($data['teams']);
    }

    // ────────────────────────────── Search ──────────────────────────────

    public function testIndexFiltersBySearchTerm(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        // Get a team name to search for
        $client->request('GET', '/api/teams?limit=1');
        $allData = json_decode($client->getResponse()->getContent(), true);

        if (empty($allData['teams'])) {
            $this->markTestSkipped('No teams in fixture data');
        }

        $teamName = $allData['teams'][0]['name'];
        // Use first few characters
        $searchTerm = substr($teamName, 0, 3);

        $client->request('GET', '/api/teams?search=' . urlencode($searchTerm));
        $searchData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertNotEmpty($searchData['teams']);
    }

    public function testIndexSearchWithNoMatchReturnsEmpty(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams?search=zzzzxxxxxnonexistent99999');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertEmpty($data['teams']);
        $this->assertEquals(0, $data['total']);
    }

    // ────────────────────────────── Non-Paginated List ──────────────────────────────

    public function testListReturnsAllTeamsForDropdown(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams/list');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('teams', $data);
        $this->assertIsArray($data['teams']);
        // List endpoint should NOT have pagination metadata
        $this->assertArrayNotHasKey('total', $data);
        $this->assertArrayNotHasKey('page', $data);
        $this->assertArrayNotHasKey('limit', $data);
    }

    public function testListTeamHasExpectedFields(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams/list');
        $data = json_decode($client->getResponse()->getContent(), true);

        if (empty($data['teams'])) {
            $this->markTestSkipped('No teams in fixture data');
        }

        $team = $data['teams'][0];
        $this->assertArrayHasKey('id', $team);
        $this->assertArrayHasKey('name', $team);
        $this->assertArrayHasKey('ageGroup', $team);
        $this->assertArrayHasKey('league', $team);
        $this->assertArrayHasKey('permissions', $team);
    }

    // ────────────────────────────── Data Structure ──────────────────────────────

    public function testIndexTeamHasExpectedFields(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams?limit=1');
        $data = json_decode($client->getResponse()->getContent(), true);

        if (empty($data['teams'])) {
            $this->markTestSkipped('No teams in fixture data');
        }

        $team = $data['teams'][0];
        $this->assertArrayHasKey('id', $team);
        $this->assertArrayHasKey('name', $team);
        $this->assertArrayHasKey('ageGroup', $team);
        $this->assertArrayHasKey('league', $team);
        $this->assertArrayHasKey('permissions', $team);

        // Check nested structure
        $this->assertArrayHasKey('id', $team['ageGroup']);
        $this->assertArrayHasKey('name', $team['ageGroup']);
        $this->assertArrayHasKey('id', $team['league']);
        $this->assertArrayHasKey('name', $team['league']);
    }

    // ────────────────────────────── Permissions ──────────────────────────────

    public function testIndexAdminHasFullPermissions(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com'); // ROLE_ADMIN

        $client->request('GET', '/api/teams?limit=1');
        $data = json_decode($client->getResponse()->getContent(), true);

        if (empty($data['teams'])) {
            $this->markTestSkipped('No teams in fixture data');
        }

        $permissions = $data['teams'][0]['permissions'];
        $this->assertTrue($permissions['canView']);
        $this->assertTrue($permissions['canEdit']);
        $this->assertTrue($permissions['canCreate']);
        $this->assertTrue($permissions['canDelete']);
    }

    public function testIndexRegularUserHasViewOnlyPermissions(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com'); // ROLE_USER

        $client->request('GET', '/api/teams?limit=1');
        $data = json_decode($client->getResponse()->getContent(), true);

        if (empty($data['teams'])) {
            $this->markTestSkipped('No teams in fixture data — user may not have team relations');
        }

        $permissions = $data['teams'][0]['permissions'];
        $this->assertTrue($permissions['canView']);
        $this->assertFalse($permissions['canEdit']);
        $this->assertFalse($permissions['canCreate']);
        $this->assertFalse($permissions['canDelete']);
    }

    public function testIndexRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/teams');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testListRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/teams/list');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    // ────────────────────────────── Combined Filters ──────────────────────────────

    public function testIndexCombinesSearchAndPagination(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams?search=a&page=1&limit=3');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertLessThanOrEqual(3, count($data['teams']));
        $this->assertEquals(1, $data['page']);
        $this->assertEquals(3, $data['limit']);
    }
}
