<?php

namespace App\Tests\Controller\Listing;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;
use Faker\Factory;
use Faker\Generator;

class EditTest extends EntityBuilder
{
    private EntityRepository $repository;
    private Generator $faker;

    public function setUp(): void
    {
        $this->repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Listing::class);
        $this->faker = Factory::create();

        self::ensureKernelShutdown();
    }

    public function testUserCanEditHisOwnListing(): void
    {
        $client = static::createClient();
        $author = $this->createUser();
        $client->loginUser($author);

        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::VERIFIED, $author);

        $oldSlug = $listing->getSlug();

        $title = $this->faker->realText(15);
        $description = $this->faker->realText(20);

        $crawler = $client->request('GET', '/listing/' . $oldSlug . '/edit');
        $form = $crawler->selectButton('Edit listing')->form([
            'listing_form[title]' => $title,
            'listing_form[description]' => $description
        ]);
        $client->submit($form);

        /** @var Listing $editedListing */
        $editedListing = $this->repository->findOneBy([
            'title' => $title,
            'description' => $description,
            'belongs_to_user' => $author->getId()
        ]);

        $this->assertNull($this->repository->findOneBy(['slug' => $oldSlug]));
        $this->assertNotNull($editedListing);
        $this->assertSame(ListingStatusEnum::NOT_VERIFIED, $editedListing->getStatus());
        $this->assertNotNull($editedListing->getEditedAt());
    }

}
