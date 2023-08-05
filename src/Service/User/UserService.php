<?php

namespace App\Service\User;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Exception\AdminDeletionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface  $tokenStorage
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

}