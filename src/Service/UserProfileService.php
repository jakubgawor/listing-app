<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserProfileService
{
    public function __construct(private EntityManagerInterface $entityManager, private TokenStorageInterface $tokenStorage)
    {
    }

    public function updateUserProfile(User $user, UserProfile $userProfile): void
    {
        $user->setUserProfile($userProfile);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user->getUserProfile());
        $this->entityManager->remove($user);

        $this->tokenStorage->setToken(null);

        $this->entityManager->flush();
    }
}