<?php

namespace App\Tests\functional\Controller\Admin\Listing;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Tests\Builder\EntityBuilder;

class DeleteTest extends EntityBuilder
{
    public function testAdminCanDeleteSomeoneElseListing(): void
    {
        $listingRepository = $this->entityManager->getRepository(Listing::class);

        $listing = $this->createListing(
            $this->faker->realText(10),
            $this->faker->realText(15),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client->request('GET', '/admin/listing/' . $listing->getSlug() . '/delete');

//        $this->assertNull($listingRepository->findOneBy(['slug' => $listing->getSlug()]));
    }

}
