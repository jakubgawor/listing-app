<?php

namespace App\Service\Listing;

use App\Entity\Listing;
use App\Entity\User;
use App\Enum\ListingStatusEnum;
use App\Exception\ListingNotFoundException;
use App\Repository\ListingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ListingService
{
    public function __construct(private EntityManagerInterface $entityManager, private ListingRepository $listingRepository)
    {
    }

    public function show(string $slug): Listing
    {
        $listing = $this->listingRepository->findOneBySlugAndStatus($slug, ListingStatusEnum::NOT_VERIFIED);

        if ($listing === null) {
            throw new ListingNotFoundException('Listing not found', 404);
        }

        return $listing;
    }

    public function createListing(Listing $listing, User $user): void
    {
        $this->entityManager->persist($listing->setBelongsToUser($user));
        $this->entityManager->flush();
    }

    public function deleteListing(Listing $listing): void
    {
        $this->entityManager->remove($listing);
        $this->entityManager->flush();
    }
}