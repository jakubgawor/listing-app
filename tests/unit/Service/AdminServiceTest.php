<?php

namespace App\Tests\unit\Service;

use App\Entity\Listing;
use App\Entity\User;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Exception\AdminDegradationException;
use App\Exception\AdminPromotionException;
use App\Exception\BanUserException;
use App\Exception\RepeatedVerificationException;
use App\Service\AdminService;
use App\Service\EmailService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class AdminServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private AdminService $adminService;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $emailService = $this->createMock(EmailService::class);

        $this->adminService = new AdminService(
            $this->entityManager,
            $this->security,
            $emailService,
        );
    }

    /** @test */
    public function verifyListing_while_listing_is_already_verified()
    {
        $listing = $this->createMock(Listing::class);
        $listing->method('getStatus')->willReturn(ListingStatusEnum::VERIFIED);

        $this->expectException(RepeatedVerificationException::class);

        $this->adminService->verifyListing($listing);
    }

    /** @test */
    public function verifyListing_is_verifying_listing_correctly()
    {
        $listing = $this->createMock(Listing::class);
        $listing->method('getStatus')->willReturn(ListingStatusEnum::NOT_VERIFIED);
        $listing->expects($this->once())->method('setStatus')->with(ListingStatusEnum::VERIFIED);

        $user = new User;
        $listing->method('getBelongsToUser')->willReturn($user);

        $result = $this->adminService->verifyListing($listing);


        $this->assertSame($listing, $result);
    }

    /** @test */
    public function promoteToAdmin_throws_AdminPromotionException_while_admin_is_already_admin()
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_ADMIN]);

        $this->expectException(AdminPromotionException::class);

        $this->adminService->promoteToAdmin($user);
    }

    /** @test */
    public function promoteToAdmin_is_promoting_correctly()
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_USER_EMAIL_VERIFIED]);

        $user->expects($this->once())->method('setRoles')->with([UserRoleEnum::ROLE_ADMIN]);
        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->adminService->promoteToAdmin($user);
    }

    /** @test */
    public function degradeToUser_throws_AdminDegradationException_if_admin_trying_to_degrade_himself()
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_ADMIN]);
        $this->security->method('getUser')->willReturn($user);

        $this->expectException(AdminDegradationException::class);

        $this->adminService->degradeToUser($user);
    }

    /** @test */
    public function degradeToUser_throws_AdminPromotionException_if_user_has_user_role()
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_USER_EMAIL_VERIFIED]);

        $this->expectException(AdminPromotionException::class);

        $this->adminService->degradeToUser($user);
    }

    /** @test */
    public function degradeToUser_is_degrading_correctly()
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_ADMIN]);

        $this->entityManager->expects($this->exactly(2))->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->adminService->degradeToUser($user);
    }

    /** @test */
    public function banUser_throws_BanUserException_if_user_is_already_banned()
    {
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(true);

        $this->expectException(BanUserException::class);

        $this->adminService->banUser($user);
    }

    /** @test */
    public function banUser_throws_BanUserException_if_user_is_admin()
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_ADMIN]);

        $this->expectException(BanUserException::class);

        $this->adminService->banUser($user);
    }

    /** @test */
    public function banUser_works_correctly()
    {
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(false);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_USER_EMAIL_VERIFIED]);
        $listing = $this->createMock(Listing::class);
        $user->method('getListings')->willReturn(new ArrayCollection([$listing]));

        $this->entityManager->expects($this->once())->method('remove')->with($listing);

        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->adminService->banUser($user);
    }

    /** @test */
    public function unbanUser_throws_BanUserException_if_the_user_is_not_banned()
    {
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(false);

        $this->expectException(BanUserException::class);

        $this->adminService->unbanUser($user);
    }

    /** @test */
    public function unbanUser_works_correctly()
    {
        $user = $this->createMock(User::class);
        $user->method('isBanned')->willReturn(true);

        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->adminService->unbanUser($user);
    }
}