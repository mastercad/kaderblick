<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\CalendarEvent;
use App\Entity\CalendarEventPermission;
use App\Entity\Club;
use App\Entity\Coach;
use App\Entity\CoachTeamAssignment;
use App\Entity\Game;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserRelation;
use App\Enum\CalendarEventPermissionType;
use App\Security\Voter\CalendarEventVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CalendarEventVoterTest extends TestCase
{
    private CalendarEventVoter $voter;

    /** @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject */
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->voter = new CalendarEventVoter($this->entityManager);
    }

    // ─── CANCEL permission tests ───

    public function testCancelAsAdminGranted(): void
    {
        $admin = $this->createUser(1, ['ROLE_ADMIN']);
        $event = $this->createEvent(2);
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CANCEL]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testCancelAsSuperadminGranted(): void
    {
        $superadmin = $this->createUser(1, ['ROLE_SUPERADMIN']);
        $event = $this->createEvent(2);
        $token = $this->createToken($superadmin);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CANCEL]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testCancelAsCreatorGranted(): void
    {
        $user = $this->createUser(1);
        $event = $this->createEvent(1); // creator ID matches user ID
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CANCEL]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testCancelAsUnrelatedUserDenied(): void
    {
        $user = $this->createUser(1);
        $event = $this->createEvent(2);
        $token = $this->createToken($user);

        // No game, no permissions
        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CANCEL]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testCancelAsCoachOfGameHomeTeamGranted(): void
    {
        $user = $this->createUser(1);
        $team = $this->createMock(Team::class);
        $game = $this->createMock(Game::class);
        $game->method('getHomeTeam')->willReturn($team);
        $game->method('getAwayTeam')->willReturn(null);

        $event = $this->createEvent(2, $game);

        $token = $this->createToken($user);
        $this->mockRepositoryQuery(true);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CANCEL]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testCancelAsCoachOfGameAwayTeamGranted(): void
    {
        $user = $this->createUser(1);
        $team = $this->createMock(Team::class);
        $game = $this->createMock(Game::class);
        $game->method('getHomeTeam')->willReturn(null);
        $game->method('getAwayTeam')->willReturn($team);

        $event = $this->createEvent(2, $game);

        $token = $this->createToken($user);
        $this->mockRepositoryQuery(true);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CANCEL]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testCancelAsNonCoachOfGameDenied(): void
    {
        $user = $this->createUser(1);
        $team = $this->createMock(Team::class);
        $game = $this->createMock(Game::class);
        $game->method('getHomeTeam')->willReturn($team);
        $game->method('getAwayTeam')->willReturn(null);

        $event = $this->createEvent(2, $game);

        $token = $this->createToken($user);
        $this->mockRepositoryQuery(false);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CANCEL]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testCancelAsCoachOfPermissionTeamGranted(): void
    {
        $user = $this->createUser(1);
        $team = $this->createMock(Team::class);

        $permission = $this->createMock(CalendarEventPermission::class);
        $permission->method('getPermissionType')->willReturn(CalendarEventPermissionType::TEAM);
        $permission->method('getTeam')->willReturn($team);

        /** @var ArrayCollection<int, CalendarEventPermission> $permissions */
        $permissions = new ArrayCollection([$permission]);

        $event = $this->createEvent(2, null, $permissions);

        $token = $this->createToken($user);
        $this->mockRepositoryQuery(true);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CANCEL]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testCancelAsCoachOfPermissionClubGranted(): void
    {
        $user = $this->createUser(1);
        $club = $this->createMock(Club::class);

        $permission = $this->createMock(CalendarEventPermission::class);
        $permission->method('getPermissionType')->willReturn(CalendarEventPermissionType::CLUB);
        $permission->method('getClub')->willReturn($club);

        /** @var ArrayCollection<int, CalendarEventPermission> $permissions */
        $permissions = new ArrayCollection([$permission]);

        $event = $this->createEvent(2, null, $permissions);

        $token = $this->createToken($user);
        $this->mockRepositoryQuery(true);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CANCEL]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    // ─── CREATE/EDIT/DELETE - admin/superadmin/supporter tests ───

    public function testCreateAsAdminGranted(): void
    {
        $admin = $this->createUser(1, ['ROLE_ADMIN']);
        $event = $this->createEvent(2);
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testCreateAsSupporterGranted(): void
    {
        $supporter = $this->createUser(1, ['ROLE_SUPPORTER']);
        $event = $this->createEvent(2);
        $token = $this->createToken($supporter);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testCreateAsNormalUserDenied(): void
    {
        // ROLE_USER without any coach assignment must be denied
        $user = $this->createUser(1);
        $event = $this->createEvent(2);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testCreateAsCoachGranted(): void
    {
        // Build chain: user -> UserRelation -> Coach -> non-empty CoachTeamAssignments
        $teamAssignment = $this->createMock(CoachTeamAssignment::class);
        $teamAssignments = new ArrayCollection([$teamAssignment]);

        $coach = $this->createMock(Coach::class);
        $coach->method('getCoachTeamAssignments')->willReturn($teamAssignments);

        $relation = $this->createMock(UserRelation::class);
        $relation->method('getCoach')->willReturn($coach);

        $user = $this->createUser(1, ['ROLE_USER'], new ArrayCollection([$relation]));
        $event = $this->createEvent(2);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testDeleteAsSuperadminGranted(): void
    {
        $superadmin = $this->createUser(1, ['ROLE_SUPERADMIN']);
        $event = $this->createEvent(2);
        $token = $this->createToken($superadmin);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    // ─── Unsupported attribute ───

    public function testUnsupportedAttributeAbstains(): void
    {
        $user = $this->createUser(1);
        $event = $this->createEvent(2);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $event, ['SOME_UNKNOWN_ATTR']);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    // ─── No user on token ───

    public function testNoUserDenied(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $event = $this->createEvent(1);

        $result = $this->voter->vote($token, $event, [CalendarEventVoter::CANCEL]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    // ─── Helper methods ───

    /**
     * @param array<string>               $roles
     * @param Collection<int, mixed>|null $userRelations
     *
     * @return User&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createUser(int $id, array $roles = ['ROLE_USER'], ?Collection $userRelations = null): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($id);
        $user->method('getRoles')->willReturn($roles);
        $user->method('getUserRelations')->willReturn($userRelations ?? new ArrayCollection());

        return $user;
    }

    /**
     * @param Collection<int, CalendarEventPermission>|null $permissions
     *
     * @return CalendarEvent&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createEvent(
        int $createdById,
        ?Game $game = null,
        ?Collection $permissions = null,
    ): CalendarEvent {
        $createdBy = $this->createMock(User::class);
        $createdBy->method('getId')->willReturn($createdById);

        $event = $this->createMock(CalendarEvent::class);
        $event->method('getCreatedBy')->willReturn($createdBy);
        $event->method('getGame')->willReturn($game);
        $event->method('getPermissions')->willReturn($permissions ?? new ArrayCollection());

        return $event;
    }

    private function createToken(User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }

    private function mockRepositoryQuery(bool $found): void
    {
        $query = $this->createMock(Query::class);
        $query->method('getOneOrNullResult')->willReturn($found ? new stdClass() : null);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('innerJoin')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('createQueryBuilder')->willReturn($qb);

        $this->entityManager->method('getRepository')
            ->willReturn($repo);
    }
}
