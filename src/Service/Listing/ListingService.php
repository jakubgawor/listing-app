<?php

namespace App\Service\Listing;

use App\Entity\Listing;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ListingService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createListing(Listing $listing, User $user)
    {
        $this->entityManager->persist($listing->setBelongsToUser($user));
        $this->entityManager->flush();
    }
}