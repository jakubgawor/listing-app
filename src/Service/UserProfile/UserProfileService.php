<?php

namespace App\Service\UserProfile;

use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;

class UserProfileService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function updateUserProfile(User $user, UserProfile $userProfile): void
    {
        $user->setUserProfile($userProfile);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

}