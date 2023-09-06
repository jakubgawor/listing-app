<?php

namespace App\Tests\unit\Service;

use App\Entity\Listing;
use App\Entity\User;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Exception\BanUserException;
use App\Exception\UnauthorizedAccessException;
use App\Service\EmailService;
use App\Service\ListingService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class ListingServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private EmailService $emailService;
    private ListingService $listingService;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->emailService = $this->createMock(EmailService::class);

        $this->listingService = new ListingService(
            $this->entityManager,
            $this->security,
            $this->emailService
        );
    }

    /** @test */
    public function showOne_throws_UnauthorizedAccessException_if_listing_is_not_verified()
    {
        $listing = $this->createMock(Listing::class);
        $listing->method('getStatus')->willReturn(ListingStatusEnum::NOT_VERIFIED);

        $this->expectException(UnauthorizedAccessException::class);

        $this->listingService->showOne($listing);
    }

    /** @test */
    public function showOne_works_correctly()
    {
        $listing = $this->createMock(Listing::class);
        $listing->method('getStatus')->willReturn(ListingStatusEnum::VERIFIED);

        $this->entityManager->expects($this->once())->method('persist')->with($listing);
        $this->entityManager->expects($this->once())->method('flush');

        $this->listingService->showOne($listing);
    }

    /** @test */
    public function create_throws_BanUserException_if_user_is_banned()
    {
        $listing = $this->createMock(Listing::class);
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(true);

        $this->expectException(BanUserException::class);

        $this->listingService->create($listing, $user);
    }

    /** @test */
    public function create_works_correctly_if_user_is_admin()
    {
        $listing = $this->createMock(Listing::class);
        $listing->method('getSlug')->willReturn('slug');
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(false);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_ADMIN]);

        $this->entityManager->expects($this->exactly(2))->method('persist')->with($listing);
        $this->entityManager->expects($this->once())->method('flush');
        $this->emailService->expects($this->once())->method('notifyAdminAboutNewListing')->with('slug');

        $this->listingService->create($listing, $user);
    }

    /** @test */
    public function create_works_correctly()
    {
        $listing = $this->createMock(Listing::class);
        $listing->method('getSlug')->willReturn('slug');
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(false);

        $this->entityManager->expects($this->once())->method('flush');
        $this->emailService->expects($this->once())->method('notifyAdminAboutNewListing')->with('slug');

        $this->listingService->create($listing, $user);
    }

    /** @test */
    public function edit_works_correctly_if_user_is_admin()
    {
        $listing = $this->createMock(Listing::class);
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_ADMIN]);

        $this->security->method('getUser')->willReturn($user);

        $this->entityManager->expects($this->exactly(2))->method('persist')->with($listing);
        $this->entityManager->expects($this->once())->method('flush');

        $this->listingService->edit($listing, $user);
    }

    /** @test */
    public function edit_works_correctly()
    {
        $listing = $this->createMock(Listing::class);
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_USER_EMAIL_VERIFIED]);

        $this->security->method('getUser')->willReturn($user);

        $this->entityManager->expects($this->exactly(2))->method('persist')->with($listing);
        $this->entityManager->expects($this->once())->method('flush');

        $this->listingService->edit($listing, $user);
    }
}