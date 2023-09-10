<?php

namespace App\Tests\integration\Repository;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Tests\Utils\EntityBuilder;

class ListingRepositoryTest extends EntityBuilder
{
    /** @test */
    public function findVerifiedBySlug_works_correctly()
    {
        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(15),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(
                uniqid(),
                $this->createUser()
            )
        );

        $slug = $listing->getSlug();

        $foundListing = $this->listingRepository->findVerifiedBySlug($slug);


        $this->assertSame($listing->getId(), $foundListing->getId());
    }

    /** @test */
    public function findVerified_works_correctly()
    {
        $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(15),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(
                uniqid(),
                $this->createUser()
            )
        );

        $listings = $this->listingRepository->findVerified();

        $this->assertContainsOnlyInstancesOf(Listing::class, $listings);
        $this->assertSame(ListingStatusEnum::VERIFIED, $listings[0]->getStatus());
        $this->assertNotEmpty($listings);
    }

    /** @test */
    public function findNotVerified_works_correctly()
    {
        $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(15),
            ListingStatusEnum::NOT_VERIFIED,
            $this->createUser(),
            $this->createCategory(
                uniqid(),
                $this->createUser()
            )
        );

        $listings = $this->listingRepository->findNotVerified();

        $this->assertContainsOnlyInstancesOf(Listing::class, $listings);
        $this->assertSame(ListingStatusEnum::NOT_VERIFIED, $listings[0]->getStatus());
        $this->assertNotEmpty($listings);
    }

    /** @test */
    public function findOneBySlug_works_correctly()
    {
        $verifiedListing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(15),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(
                uniqid(),
                $this->createUser()
            )
        );

        $notVerifiedListing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(15),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(
                uniqid(),
                $this->createUser()
            )
        );

        $foundVerifiedListing = $this->listingRepository->findOneBySlug($verifiedListing->getSlug());
        $foundNotVerifiedListing = $this->listingRepository->findOneBySlug($notVerifiedListing->getSlug());


        $this->assertSame($verifiedListing->getId(), $foundVerifiedListing->getId());
        $this->assertSame($notVerifiedListing->getId(), $foundNotVerifiedListing->getId());
    }
}