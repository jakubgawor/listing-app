<?php

namespace App\Service;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Exception\RepeatedVerificationException;
use App\Service\Listing\ListingService;

class AdminService
{
    public function __construct(
        private readonly ListingService $listingService
    )
    {
    }

    public function verifyListing(Listing $listing): Listing
    {
        if ($listing->getStatus() === ListingStatusEnum::VERIFIED) {
            throw new RepeatedVerificationException('This listing is already verified!');
        }
        $this->listingService->verifyListing($listing);

        return $listing;
    }
}