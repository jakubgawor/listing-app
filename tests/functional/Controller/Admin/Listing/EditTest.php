<?php

namespace App\Tests\functional\Controller\Admin\Listing;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Tests\Builder\EntityBuilder;

class EditTest extends EntityBuilder
{
    public function testAdminCanEditSomeoneListing(): void
    {
        $listingRepository = $this->entityManager->getRepository(Listing::class);
        $author = $this->createUser();

        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::VERIFIED,
            $author,
            $this->createCategory(uniqid(), $this->createUser())
        );

        $oldSlug = $listing->getSlug();

        $title = $this->faker->realText(15);
        $description = $this->faker->realText(20);

        $crawler = $this->client
            ->request('GET', '/admin/listing/' . $oldSlug . '/edit');
        $form = $crawler->selectButton('Edit listing')->form([
            'listing_form[title]' => $title,
            'listing_form[description]' => $description
        ]);
        $this->client->submit($form);

        /** @var Listing $editedListing */
        $editedListing = $listingRepository->findOneBy([
            'title' => $title,
            'description' => $description,
            'belongs_to_user' => $author->getId()
        ]);

        $this->assertNull($listingRepository->findOneBy(['slug' => $oldSlug]));
        $this->assertSame(ListingStatusEnum::VERIFIED, $editedListing->getStatus());
        $this->assertNotNull($editedListing);
        $this->assertNotNull($editedListing->getEditedAt());
    }

}
