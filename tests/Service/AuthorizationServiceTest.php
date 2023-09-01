<?php

namespace App\Tests\Service;

use App\Entity\Listing;
use App\Entity\User;
use App\Enum\ListingStatusEnum;
use App\Exception\NotVerifiedListingException;
use App\Exception\UnauthorizedAccessException;
use App\Service\AuthorizationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthorizationServiceTest extends TestCase
{
    private AuthorizationService $authorizationService;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->authorizationService = new AuthorizationService($this->authorizationChecker);
    }

    /** @test */
    public function denyUnauthorizedUserAccess_throws_UnauthorizedAccessException_to_unauthorized_user()
    {
        $user = $this->createMock(User::class);

        $this->expectException(UnauthorizedAccessException::class);

        $this->authorizationService->denyUnauthorizedUserAccess($user);
    }

    /** @test */
    public function denyUnauthorizedUserAccess_while_access_is_granted()
    {
        $user = $this->createMock(User::class);
        $user->method('getUserIdentifier')->willReturn('username');
        $this->authorizationChecker->method('isGranted')->willReturn(true);

        $this->authorizationService->denyUnauthorizedUserAccess($user);

        $this->assertTrue(true);
    }

    /** @test */
    public function denyLoggedUserAccess_throws_UnauthorizedAccessException_while_user_is_logged_in()
    {
        $user = $this->createMock(User::class);

        $this->expectException(UnauthorizedAccessException::class);

        $this->authorizationService->denyLoggedUserAccess($user);
    }

    /** @test */
    public function denyLoggedUserAccess_while_user_is_not_logged_in()
    {
        $this->authorizationService->denyLoggedUserAccess(null);

        $this->assertTrue(true);
    }

    /** @test */
    public function denyUserAccessToNotVerifiedListings_throws_NotVerifiedListingException_if_listing_is_not_verified()
    {
        $listing = $this->createMock(Listing::class);
        $listing->method('getStatus')->willReturn(ListingStatusEnum::NOT_VERIFIED);

        $this->expectException(NotVerifiedListingException::class);

        $this->authorizationService->denyUserAccessToNotVerifiedListings($listing);
    }

    /** @test */
    public function denyUserAccessToNotVerifiedListings_if_the_listing_is_verified()
    {
        $listing = $this->createMock(Listing::class);
        $listing->method('getStatus')->willReturn(ListingStatusEnum::VERIFIED);

        $this->authorizationService->denyUserAccessToNotVerifiedListings($listing);

        $this->assertTrue(true);
    }
}