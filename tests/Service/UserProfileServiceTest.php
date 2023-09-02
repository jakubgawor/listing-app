<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Service\UserProfileService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UserProfileServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UserProfileService $userProfileService;
    private UserService $userService;


    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userService = $this->createMock(UserService::class);

        $this->userProfileService = new UserProfileService(
            $this->entityManager,
            $this->userService
        );
    }

    /** @test */
    public function updateUserProfile_works_correctly_with_email_change()
    {
        $user = $this->createMock(User::class);
        $userProfile = $this->createMock(UserProfile::class);
        $userProfile->method('getUser')->willReturn($user);
        $originalEmail = 'other_email@example.com';

        $this->userService->expects($this->once())->method('changeEmail')->with($user);
        $user->expects($this->once())->method('setUserProfile')->with($userProfile);
        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->userProfileService->updateUserProfile($userProfile, $user, $originalEmail);
    }

    /** @test */
    public function updateUserProfile_works_correctly_without_email_change()
    {
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('example@example.com');

        $userProfile = $this->createMock(UserProfile::class);
        $userProfile->method('getUser')->willReturn($user);

        $originalEmail = 'example@example.com';

        $user->expects($this->once())->method('setUserProfile')->with($userProfile);
        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->userProfileService->updateUserProfile($userProfile, $user, $originalEmail);
    }

}