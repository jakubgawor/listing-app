<?php

namespace App\Tests\Controller\Listing;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;
use Faker\Factory;
use Faker\Generator;

class CreateTest extends EntityBuilder
{
    private EntityRepository $repository;
    private Generator $faker;

    public function setUp(): void
    {
        $this->repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Listing::class);
        $this->faker = Factory::create();

        self::ensureKernelShutdown();
    }

    public function testUserCanCreateNewListing(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $title = $this->faker->realText(10);
        $description = $this->faker->realText(15);

        $crawler = $client->request('GET', '/create-listing');

        $form = $crawler->selectButton('Submit')->form([
            'listing_form[title]' => $title,
            'listing_form[description]' => $description
        ]);
        $client->submit($form);

        /** @var Listing $listing */
        $listing = $this->repository->findOneBy([
            'title' => $title,
            'description' => $description,
            'belongs_to_user' => $user
        ]);

        $this->assertNotNull($listing);
        $this->assertSame(ListingStatusEnum::NOT_VERIFIED, $listing->getStatus());
        $this->assertSame(null, $listing->getEditedAt());
    }

    public function testUserCanCreateNewListingIfTitleAndDescriptionExists(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $title = $this->faker->realText(10);
        $description = $this->faker->realText(15);

        $existingListing = $this->createListing($title, $description, ListingStatusEnum::VERIFIED, $this->createUser());
        $listing = $this->createListing($title, $description, ListingStatusEnum::VERIFIED, $user);

        $this->assertNotNull($this->repository->findOneBy(['slug' => $listing->getSlug()]));
        $this->assertNotSame($existingListing->getSlug(), $listing->getSlug());
    }

}
