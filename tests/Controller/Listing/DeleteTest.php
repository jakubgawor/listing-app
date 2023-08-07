<?php

namespace App\Tests\Controller\Listing;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;
use Faker\Factory;
use Faker\Generator;

class DeleteTest extends EntityBuilder
{
    private EntityRepository $repository;
    private Generator $faker;

    public function setUp(): void
    {
        $this->repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Listing::class);
        $this->faker = Factory::create();

        self::ensureKernelShutdown();
    }

    public function testUserCanDeleteHisOwnVerifiedListing(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::VERIFIED, $user);

        $client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertResponseRedirects('/', 302);
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('success'));
        $this->assertNull($listing->getId());
    }

    public function testUserCanNotDeleteNotVerifiedListing(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::NOT_VERIFIED, $user);

        $client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertResponseRedirects('/', 302);
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('notification'));
    }

    public function testUserCanNotDeleteSomeoneElseListing(): void
    {
        $client = static::createClient();
        $author = $this->createUser();
        $client->loginUser($author);

        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::VERIFIED, $this->createUser());

        $client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertResponseRedirects('/', 302);
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('error'));
        $this->assertNotNull($this->repository->findOneBy([
            'slug' => $listing->getSlug()
        ]));
    }

    public function testNotLoggedUserCanNotDeleteListings(): void
    {
        $client = static::createClient();

        $listing = $this->createListing($this->faker->realText(20), $this->faker->realText(50), ListingStatusEnum::VERIFIED, $this->createUser());

        $client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertResponseRedirects('/login', 302);
        $this->assertNotNull($this->repository->findOneBy([
            'slug' => $listing->getSlug()
        ]));
    }

    public function testUserCanNotDeleteNotExistingListing(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/listing/not-exist/delete');

        $this->assertResponseRedirects('/', 302);
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('error'));
    }
}
