<?php

namespace Tests\Feature\Controller;

use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\ApiWebTestCase;

class CoachesControllerTest extends ApiWebTestCase
{
    // ────────────────────────────── Pagination ──────────────────────────────

    public function testListReturnsPaginatedStructure(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com'); // ROLE_ADMIN

        $client->request('GET', '/api/coaches');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('coaches', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertIsArray($data['coaches']);
        $this->assertIsInt($data['total']);
        $this->assertEquals(1, $data['page']);
        $this->assertEquals(25, $data['limit']);
    }

    public function testListDefaultsToPage1Limit25(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/coaches');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(1, $data['page']);
        $this->assertEquals(25, $data['limit']);
        $this->assertLessThanOrEqual(25, count($data['coaches']));
    }

    public function testListRespectsCustomPageAndLimit(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/coaches?page=1&limit=5');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(1, $data['page']);
        $this->assertEquals(5, $data['limit']);
        $this->assertLessThanOrEqual(5, count($data['coaches']));
    }

    public function testListLimitIsCappedAt100(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/coaches?limit=500');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(100, $data['limit']);
        $this->assertLessThanOrEqual(100, count($data['coaches']));
    }

    public function testListPageMinimumIs1(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/coaches?page=-1');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(1, $data['page']);
    }

    public function testListPaginationReturnsConsistentTotal(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        // Fetch first page with small limit
        $client->request('GET', '/api/coaches?page=1&limit=3');
        $data1 = json_decode($client->getResponse()->getContent(), true);
        $total = $data1['total'];

        // Fetch second page
        $client->request('GET', '/api/coaches?page=2&limit=3');
        $data2 = json_decode($client->getResponse()->getContent(), true);

        // Total should be the same across pages
        $this->assertEquals($total, $data2['total']);

        // If total > 3, second page should have results
        if ($total > 3) {
            $this->assertNotEmpty($data2['coaches']);
        }
    }

    public function testListBeyondLastPageReturnsEmpty(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/coaches?page=99999&limit=25');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertEmpty($data['coaches']);
        $this->assertGreaterThanOrEqual(0, $data['total']);
    }

    // ────────────────────────────── Search ──────────────────────────────

    public function testListFiltersBySearchTerm(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        // Get a coach name to search for
        $client->request('GET', '/api/coaches?limit=1');
        $allData = json_decode($client->getResponse()->getContent(), true);

        if (empty($allData['coaches'])) {
            $this->markTestSkipped('No coaches in fixture data');
        }

        $firstName = $allData['coaches'][0]['firstName'];

        $client->request('GET', '/api/coaches?search=' . urlencode($firstName));
        $searchData = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertNotEmpty($searchData['coaches']);
        foreach ($searchData['coaches'] as $coach) {
            $fullName = strtolower($coach['firstName'] . ' ' . $coach['lastName']);
            $this->assertStringContainsString(strtolower($firstName), $fullName);
        }
    }

    public function testListSearchWithNoMatchReturnsEmpty(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/coaches?search=zzzzxxxxxnonexistent99999');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertEmpty($data['coaches']);
        $this->assertEquals(0, $data['total']);
    }

    // ────────────────────────────── Team Filter ──────────────────────────────

    public function testListFiltersByTeamId(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        // Get a team ID
        $client->request('GET', '/api/teams/list');
        $teamsData = json_decode($client->getResponse()->getContent(), true);

        if (empty($teamsData['teams'])) {
            $this->markTestSkipped('No teams in fixture data');
        }

        $teamId = $teamsData['teams'][0]['id'];

        $client->request('GET', '/api/coaches?teamId=' . $teamId);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('coaches', $data);

        // Total with team filter should be <= total without filter
        $client->request('GET', '/api/coaches');
        $allData = json_decode($client->getResponse()->getContent(), true);
        $this->assertLessThanOrEqual($allData['total'], $data['total']);
    }

    // ────────────────────────────── Data Structure ──────────────────────────────

    public function testListCoachHasExpectedFields(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/coaches?limit=1');
        $data = json_decode($client->getResponse()->getContent(), true);

        if (empty($data['coaches'])) {
            $this->markTestSkipped('No coaches in fixture data');
        }

        $coach = $data['coaches'][0];
        $this->assertArrayHasKey('id', $coach);
        $this->assertArrayHasKey('firstName', $coach);
        $this->assertArrayHasKey('lastName', $coach);
        $this->assertArrayHasKey('email', $coach);
        $this->assertArrayHasKey('permissions', $coach);
        $this->assertArrayHasKey('clubAssignments', $coach);
        $this->assertArrayHasKey('teamAssignments', $coach);
        $this->assertArrayHasKey('licenseAssignments', $coach);
        $this->assertArrayHasKey('nationalityAssignments', $coach);
    }

    // ────────────────────────────── Permissions ──────────────────────────────

    public function testListAdminHasFullPermissions(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com'); // ROLE_ADMIN

        $client->request('GET', '/api/coaches?limit=1');
        $data = json_decode($client->getResponse()->getContent(), true);

        if (empty($data['coaches'])) {
            $this->markTestSkipped('No coaches in fixture data');
        }

        $permissions = $data['coaches'][0]['permissions'];
        $this->assertTrue($permissions['canView']);
        $this->assertTrue($permissions['canEdit']);
        $this->assertTrue($permissions['canCreate']);
        $this->assertTrue($permissions['canDelete']);
    }

    public function testListRegularUserHasViewOnlyPermissions(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user6@example.com'); // ROLE_USER

        $client->request('GET', '/api/coaches?limit=1');
        $data = json_decode($client->getResponse()->getContent(), true);

        if (empty($data['coaches'])) {
            $this->markTestSkipped('No coaches in fixture data');
        }

        $permissions = $data['coaches'][0]['permissions'];
        $this->assertTrue($permissions['canView']);
        $this->assertFalse($permissions['canEdit']);
        $this->assertFalse($permissions['canCreate']);
        $this->assertFalse($permissions['canDelete']);
    }

    public function testListRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/coaches');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    // ────────────────────────────── Combined Filters ──────────────────────────────

    public function testListCombinesSearchAndTeamFilter(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/teams/list');
        $teamsData = json_decode($client->getResponse()->getContent(), true);

        if (empty($teamsData['teams'])) {
            $this->markTestSkipped('No teams in fixture data');
        }

        $teamId = $teamsData['teams'][0]['id'];

        $client->request('GET', '/api/coaches?teamId=' . $teamId . '&search=a');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('coaches', $data);
        $this->assertArrayHasKey('total', $data);
    }

    public function testListCombinesSearchAndPagination(): void
    {
        $client = static::createClient();
        $this->authenticateUser($client, 'user16@example.com');

        $client->request('GET', '/api/coaches?search=a&page=1&limit=3');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertLessThanOrEqual(3, count($data['coaches']));
        $this->assertEquals(1, $data['page']);
        $this->assertEquals(3, $data['limit']);
    }
}
