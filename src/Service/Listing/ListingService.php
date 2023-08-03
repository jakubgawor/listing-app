<?php

namespace App\Service\Listing;

use App\Entity\Listing;
use App\Entity\User;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Exception\ListingNotFoundException;
use App\Repository\ListingRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ListingService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ListingRepository      $listingRepository
    )
    {
    }

    public function find(string $slug): Listing
    {
        $listing = $this->listingRepository->findVerifiedBySlug($slug);

        if ($listing === null) {
            throw new ListingNotFoundException('Listing not found', 404);
        }

        return $listing;
    }

    public function create(Listing $listing, User $user): void
    {
        if (in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            $this->entityManager->persist($listing->setStatus(ListingStatusEnum::VERIFIED));
        }

        $this->entityManager->persist($listing->setBelongsToUser($user));

        $this->entityManager->flush();
    }

    public function edit(Listing $listing, User $user): void
    {
        if (!in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            $this->entityManager->persist($listing->setStatus(ListingStatusEnum::NOT_VERIFIED));
        }

        $this->entityManager->persist($listing->setEditedAt(new DateTime));

        $this->entityManager->flush();
    }

    public function deleteListing(Listing $listing): void
    {
        $this->entityManager->remove($listing);
        $this->entityManager->flush();
    }

    public function verifyListing(Listing $listing): void
    {
        $this->entityManager->persist($listing->setStatus(ListingStatusEnum::VERIFIED));
        $this->entityManager->flush();
    }

}