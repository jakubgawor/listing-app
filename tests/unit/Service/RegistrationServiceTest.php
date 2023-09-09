<?php

namespace App\Tests\unit\Service;

use App\Entity\User;
use App\Exception\UserNotRegisteredException;
use App\Exception\VerifyEmailException;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\EmailService;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;
    private EmailService $emailService;
    private UserRepository $userRepository;
    private EmailVerifier $emailVerifier;
    private RegistrationService $registrationService;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userPasswordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->emailService = $this->createMock(EmailService::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->emailVerifier = $this->createMock(EmailVerifier::class);

        $this->registrationService = new RegistrationService(
            $this->entityManager,
            $this->userPasswordHasher,
            $this->emailService,
            $this->userRepository,
            $this->emailVerifier
        );
    }

    /** @test */
    public function registerUser_works_correctly()
    {
        $user = $this->createMock(User::class);
        $plainPassword = 'password';

        $this->userPasswordHasher->expects($this->once())->method('hashPassword')->willReturn('hashedPassword');
        $this->entityManager->expects($this->exactly(2))->method('persist');
        $this->entityManager->expects($this->once())->method('flush');
        $this->emailService->expects($this->once())->method('sendRegistrationEmailConfirmation');

        $this->registrationService->registerUser($user, $plainPassword);
    }

    /** @test */
    public function verifyEmailAddress_works_correctly()
    {
        $request = $this->createMock(Request::class);
        $request->query = $this->createMock(ParameterBag::class);
        $request->query->method('get')->with('id')->willReturn(1);

        $user = $this->createMock(User::class);

        $this->userRepository->expects($this->once())->method('find')->willReturn($user);
        $this->emailVerifier->expects($this->once())->method('handleEmailConfirmation');

        $this->registrationService->verifyEmailAddress($request);
    }

    /** @test */
    public function verifyEmailAddress_throws_UserNotRegisteredException_when_id_is_null()
    {
        $request = $this->createMock(Request::class);
        $request->query = $this->createMock(ParameterBag::class);

        $this->expectException(UserNotRegisteredException::class);

        $this->registrationService->verifyEmailAddress($request);
    }

    /** @test */
    public function verifyEmailAddress_throws_VerifyEmailException()
    {
        $request = $this->createMock(Request::class);
        $request->query = $this->createMock(ParameterBag::class);
        $request->query->method('get')->with('id')->willReturn(1);

        $user = $this->createMock(User::class);

        $this->userRepository->expects($this->once())->method('find')->willReturn($user);
        $this->emailVerifier->expects($this->once())
            ->method('handleEmailConfirmation')
            ->will($this->throwException($this->createMock(VerifyEmailException::class)));

        $this->expectException(VerifyEmailException::class);

        $this->registrationService->verifyEmailAddress($request);
    }

}