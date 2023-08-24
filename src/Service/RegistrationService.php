<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Enum\UserRoleEnum;
use App\Exception\UserNotRegisteredException;
use App\Exception\VerifyEmailException;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationService
{
    public function __construct(
        private EntityManagerInterface      $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EmailService                $emailService,
        private UserRepository              $userRepository,
        private EmailVerifier               $emailVerifier
    )
    {
    }

    public function registerUser(User $user, string $plainPassword): void
    {
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
        $user->setRoles([UserRoleEnum::ROLE_USER]);
        $this->entityManager->persist($user);

        $userProfile = new UserProfile();
        $userProfile->setUser($user);
        $this->entityManager->persist($userProfile);

        $this->entityManager->flush();

        $this->emailService->sendRegistrationEmailConfirmation($user);
    }

    public function verifyEmailAddress(Request $request): void
    {
        $id = $request->query->get('id');

        if (null === $id) {
            throw new UserNotRegisteredException();
        }

        $user = $this->userRepository->find($id);

        try {
            $user->setRoles([UserRoleEnum::ROLE_USER_EMAIL_VERIFIED]);
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            throw new VerifyEmailException($exception->getReason());
        }
    }
}