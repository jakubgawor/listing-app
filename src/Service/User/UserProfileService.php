<?php

namespace App\Service\User;

use App\Entity\Interface\EntityMarkerInterface;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Service\Interface\EntityServiceInterface;
use App\Traits\EntityCheckerTrait;
use Doctrine\ORM\EntityManagerInterface;

class UserProfileService implements EntityServiceInterface
{
    use EntityCheckerTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function handleEntity(User $user, EntityMarkerInterface $entity): void
    {
        $this->checkEntityType($entity, UserProfile::class);

        $this->updateUserProfile($entity, $user);

    }

    public function updateUserProfile(UserProfile $userProfile, User $user): void
    {
        $user->setUserProfile($userProfile);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

}