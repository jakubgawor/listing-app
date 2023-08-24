<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Exception\AdminDeletionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface  $tokenStorage,
        private readonly EmailService           $emailService
    )
    {
    }

    public function deleteUser(User $user): void
    {
        if (in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            throw new AdminDeletionException('You can not delete users with admin role', 403);
        }

        $this->entityManager->remove($user->getUserProfile());
        $this->entityManager->remove($user);

        $this->tokenStorage->setToken(null);

        $this->entityManager->flush();
    }

    public function changeEmail(User $user): void
    {
        $user
            ->setIsVerified(false)
            ->setRoles([UserRoleEnum::ROLE_USER]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->emailService->sendRegistrationEmailConfirmation($user);
    }

}