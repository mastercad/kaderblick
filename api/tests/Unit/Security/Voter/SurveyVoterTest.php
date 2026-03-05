<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Survey;
use App\Entity\User;
use App\Security\Voter\SurveyVoter;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SurveyVoterTest extends TestCase
{
    private SurveyVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new SurveyVoter();
    }

    // --- VIEW ---

    public function testViewAsRegularUserReturnsGranted(): void
    {
        $user = $this->createUser(1);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testViewAsGuestReturnsGranted(): void
    {
        $user = $this->createUser(1, ['ROLE_GUEST']);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testViewAsAdminReturnsGranted(): void
    {
        $user = $this->createUser(1, ['ROLE_ADMIN']);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testViewWithoutUserReturnsDenied(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);
        $survey = new Survey();

        $result = $this->voter->vote($token, $survey, [SurveyVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    // --- CREATE ---

    public function testCreateAsAdminReturnsGranted(): void
    {
        $user = $this->createUser(1, ['ROLE_ADMIN']);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testCreateAsSuperAdminReturnsGranted(): void
    {
        $user = $this->createUser(1, ['ROLE_SUPERADMIN']);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testCreateAsRegularUserReturnsDenied(): void
    {
        $user = $this->createUser(1);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testCreateAsGuestReturnsDenied(): void
    {
        $user = $this->createUser(1, ['ROLE_GUEST']);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    // --- EDIT ---

    public function testEditAsAdminReturnsGranted(): void
    {
        $user = $this->createUser(1, ['ROLE_ADMIN']);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testEditAsSuperAdminReturnsGranted(): void
    {
        $user = $this->createUser(1, ['ROLE_SUPERADMIN']);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testEditAsRegularUserReturnsDenied(): void
    {
        $user = $this->createUser(1);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::EDIT]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    // --- DELETE ---

    public function testDeleteAsAdminReturnsGranted(): void
    {
        $user = $this->createUser(1, ['ROLE_ADMIN']);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testDeleteAsSuperAdminReturnsGranted(): void
    {
        $user = $this->createUser(1, ['ROLE_SUPERADMIN']);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testDeleteAsRegularUserReturnsDenied(): void
    {
        $user = $this->createUser(1);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, [SurveyVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    // --- Unsupported attribute ---

    public function testUnsupportedAttributeReturnsAbstain(): void
    {
        $user = $this->createUser(1, ['ROLE_ADMIN']);
        $survey = new Survey();
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $survey, ['SOME_UNSUPPORTED_ATTR']);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testUnsupportedSubjectReturnsAbstain(): void
    {
        $user = $this->createUser(1, ['ROLE_ADMIN']);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, new stdClass(), [SurveyVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    // --- Helpers ---

    /**
     * @param array<string> $roles
     */
    private function createUser(int $id, array $roles = ['ROLE_USER']): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($id);
        $user->method('getRoles')->willReturn($roles);

        return $user;
    }

    private function createToken(User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }
}
