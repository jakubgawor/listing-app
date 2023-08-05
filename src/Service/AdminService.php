<?php

namespace App\Service;

use App\Entity\Listing;
use App\Entity\User;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Exception\AdminPromotionException;
use App\Exception\ListingNotFoundException;
use App\Exception\RepeatedVerificationException;
use App\Service\Listing\ListingService;
use Doctrine\ORM\EntityManagerInterface;

class AdminService
{
    public function __construct(
        private readonly ListingService $listingService,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function verifyListing(?Listing $listing): Listing
    {
        if (!$listing) {
            throw new ListingNotFoundException('Listing not found');
        }

        if ($listing->getStatus() === ListingStatusEnum::VERIFIED) {
            throw new RepeatedVerificationException('This listing is already verified!');
        }
        $this->listingService->verifyListing($listing);

        return $listing;
    }

    public function promoteToAdmin(User $user): void
    {
        if(in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            throw new AdminPromotionException('You can not promote an admin!');
        }

        $this->entityManager->persist($user->setRoles([UserRoleEnum::ROLE_ADMIN]));
        $this->entityManager->flush();
    }

    public function degradeToUser(User $user): void
    {
        if (!in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            throw new AdminPromotionException('You can not degrade an user!');
        }

        $this->entityManager->persist($user->setRoles([UserRoleEnum::ROLE_USER_EMAIL_VERIFIED]));
        $this->entityManager->persist($user->setIsVerified(true));
        $this->entityManager->flush();
    }
}