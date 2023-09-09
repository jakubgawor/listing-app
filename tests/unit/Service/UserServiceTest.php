<?php

namespace App\Tests\unit\Service;

use App\DTO\ChangePasswordDTO;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Enum\UserRoleEnum;
use App\Exception\AdminDeletionException;
use App\Service\EmailService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;
    private EmailService $emailService;
    private UserPasswordHasherInterface $userPasswordHasher;
    private UserService $userService;


    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->emailService = $this->createMock(EmailService::class);
        $this->userPasswordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->userService = new UserService(
            $this->entityManager,
            $this->tokenStorage,
            $this->emailService,
            $this->userPasswordHasher
        );
    }

    /** @test */
    public function deleteUser_throws_AdminDeletionException_if_user_is_admin()
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_ADMIN]);

        $this->expectException(AdminDeletionException::class);

        $this->userService->deleteUser($user);
    }

    /** @test */
    public function deleteUser_works_correctly_if_user_is_not_admin()
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn([UserRoleEnum::ROLE_USER_EMAIL_VERIFIED]);
        $user->method('getUserProfile')->willReturn($this->createMock(UserProfile::class));

        $this->entityManager->expects($this->exactly(2))->method('remove');
        $this->tokenStorage->expects($this->once())->method('setToken')->with(null);
        $this->entityManager->expects($this->once())->method('flush');

        $this->userService->deleteUser($user);
    }

    /** @test */
    public function changeEmail_works_correctly()
    {
        $user = $this->createMock(User::class);

        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');
        $this->emailService->expects($this->once())->method('sendRegistrationEmailConfirmation')->with($user);

        $this->userService->changeEmail($user);
    }

    /** @test */
    public function changePassword_throws_InvalidPassword_Exception_when_password_is_not_correct()
    {
        $user = $this->createMock(User::class);
        $changePasswordDTO = $this->createMock(ChangePasswordDTO::class);
        $this->userPasswordHasher->method('isPasswordValid')->willReturn(false);

        $this->expectException(InvalidPasswordException::class);

        $this->userService->changePassword($user, $changePasswordDTO);
    }

    /** @test */
    public function changePassword_works_correctly()
    {
        $user = $this->createMock(User::class);
        $changePasswordDTO = $this->createMock(ChangePasswordDTO::class);
        $this->userPasswordHasher->method('isPasswordValid')->willReturn(true);


        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->userService->changePassword($user, $changePasswordDTO);

    }
}