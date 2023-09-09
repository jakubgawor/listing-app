<?php

namespace App\Tests\unit\Security\Voter;

use App\Entity\User;
use App\Security\Voter\SameUsernameVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SameUsernameVoterTest extends TestCase
{
    protected TokenInterface $token;
    protected SameUsernameVoter $voter;
    protected User $mockedUser;

    public function setUp(): void
    {
        $this->mockedUser = $this->createMock(User::class);
        $this->mockedUser->expects($this->any())
            ->method('getUserIdentifier')
            ->willReturn('example_username');

        $this->token = $this->createMock(TokenInterface::class);
        $this->token
            ->method('getUser')
            ->willReturn($this->mockedUser);

        $this->voter = new SameUsernameVoter();
    }

    /** @test */
    public function returns_access_granted_when_same_user_is_provided()
    {
        $result = $this->voter->vote(
            $this->token,
            'example_username',
            [SameUsernameVoter::IS_SAME_USER]
        );

        $this->assertSame(SameUsernameVoter::ACCESS_GRANTED, $result);
    }

    /** @test */
    public function returns_access_denied_when_different_user_is_provided()
    {
        $result = $this->voter->vote(
            $this->token,
            'different_user',
            [SameUsernameVoter::IS_SAME_USER]
        );

        $this->assertSame(SameUsernameVoter::ACCESS_DENIED, $result);
    }

    /** @test */
    public function returns_access_abstain_if_user_is_null()
    {
        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->method('getUser')
            ->willReturn(null);

        $result = $this->voter->vote(
            $tokenMock,
            null,
            [SameUsernameVoter::IS_SAME_USER]
        );

        $this->assertSame(SameUsernameVoter::ACCESS_ABSTAIN, $result);
    }

}