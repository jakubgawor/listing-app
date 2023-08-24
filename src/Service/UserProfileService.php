<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;

class UserProfileService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserService            $userService
    )
    {
    }

    public function updateUserProfile(UserProfile $userProfile, User $user, string $originalEmail): void
    {
        if ($userProfile->getUser()->getEmail() !== $originalEmail) {
            $this->userService->changeEmail($user);
        }

        $user->setUserProfile($userProfile);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

    }

}