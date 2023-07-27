<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserService
{
    public function __construct(private EntityManagerInterface $entityManager, private TokenStorageInterface $tokenStorage)
    {
    }

    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user->getUserProfile());
        $this->entityManager->remove($user);

        $this->tokenStorage->setToken(null);

        $this->entityManager->flush();
    }

}