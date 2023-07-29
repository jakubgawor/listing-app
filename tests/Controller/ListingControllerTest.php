<?php

namespace App\Tests\Controller;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Faker\Factory;
use Faker\Generator;

class ListingControllerTest extends EntityBuilder
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    private Generator $faker;

    public function setUp(): void
    {
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(Listing::class);
        $this->faker = Factory::create();

        self::ensureKernelShutdown();
    }

    public function testBuilder(): void
    {
        $author = $this->createUser();

        $title = $this->faker->realText(50);
        $description = $this->faker->realText(200);

        $this->createListing($title, $description, $author);

        /** @var Listing $listing */
        $listing = $this->repository->findOneBy([
            'title' => $title,
            'description' => $description
        ]);

        $this->assertNotNull($listing);
        $this->assertSame(ListingStatusEnum::NOT_VERIFIED, $listing->getStatus());
        $this->assertSame(null, $listing->getEditedAt());
    }

    public function testShowExistingListing(): void
    {
        $client = static::createClient();
        $author = $this->createUser();
        $listing = $this->createListing($this->faker->realText(20), $this->faker->realText(50), $author);

        $client->request('GET', '/listing/' . $listing->getSlug());

        $this->assertResponseIsSuccessful();
    }

    public function testShowNotExistingListing(): void
    {
        $client = static::createClient();

        $client->request('GET', '/listing/not-existing');

        $this->assertSame(500, $client->getResponse()->getStatusCode());
    }

    public function testCreateListingPageCanBeRenderedWhileUserIsLoggedIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser();

        $client->loginUser($user);

        $client->request('GET', '/create-listing');

        $this->assertResponseIsSuccessful();
    }

    public function testCreateListingPageCanNotBeRenderedWhileUserIsNotLoggedIn(): void
    {
        $client = static::createClient();

        $client->request('GET', '/create-listing');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertResponseRedirects('/login');
    }

    public function testLoggedUserCanCreateNewListing(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $title = $this->faker->realText('10');
        $description = $this->faker->realText('15');

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


        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertNotNull($listing);
        $this->assertSame($user->getId(), $listing->getBelongsToUser()->getId());
        $this->assertSame(ListingStatusEnum::NOT_VERIFIED, $listing->getStatus());
        $this->assertSame(null, $listing->getEditedAt());
    }

    public function testNotLoggedUserCanNotCreateNewListing(): void
    {
        $client = static::createClient();

        $client->request('POST', '/create-listing', [
            'title' => $this->faker->realText('10'),
            'description' => $this->faker->realText('15')
        ]);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertResponseRedirects('/login');
    }

    public function testCreateListingPageCanNotBeRenderedWhileUserHasNotVerifiedEmailAddress(): void
    {
        $client = static::createClient();
        $user = $this->createUser(null, UserRoleEnum::ROLE_USER, false);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/create-listing');

        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }

    public function testUserCanNotCreateNewListingWithNotVerifiedEmailAddress(): void
    {
        $client = static::createClient();
        $user = $this->createUser(null, UserRoleEnum::ROLE_USER, false);
        $client->loginUser($user);

        $client->request('POST', '/create-listing');

        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }

    public function testUserCanEditHisOwnListing(): void
    {
        $client = static::createClient();
        $author = $this->createUser();
        $client->loginUser($author);

        $listing = $this->createListing($this->faker->realText(20), $this->faker->realText(50), $author);

        $oldSlug = $listing->getSlug();

        $title = $this->faker->realText(10);
        $description = $this->faker->realText(15);

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


        $this->assertNull($this->repository->findOneBy([
            'slug' => $oldSlug
        ]));
        $this->assertNotNull($editedListing);
        $this->assertNotSame($editedListing->getSlug(), $oldSlug);
        $this->assertResponseRedirects('/');
    }

    public function testUserCanNotEditSomeoneElseListing(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $listing = $this->createListing($this->faker->realText(20), $this->faker->realText(50), $this->createUser());

        $client->request('GET', '/listing/' . $listing->getSlug() . '/edit');

        $this->assertSame(500, $client->getResponse()->getStatusCode());
    }

    public function testUserCanDeleteHisOwnListing(): void
    {
        $client = static::createClient();
        $author = $this->createUser();
        $client->loginUser($author);

        $listing = $this->createListing($this->faker->realText(20), $this->faker->realText(50), $author);

        $client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertResponseRedirects('/');
        $this->assertNull($listing->getId());
    }

    public function testUserCanNotDeleteSomeoneElseListing(): void
    {
        $client = static::createClient();
        $author = $this->createUser();
        $client->loginUser($author);

        $listing = $this->createListing($this->faker->realText(20), $this->faker->realText(50), $this->createUser());

        $client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertSame(500, $client->getResponse()->getStatusCode());
        $this->assertNotNull($this->repository->findOneBy([
            'slug' => $listing->getSlug()
        ]));
    }

    public function testNotLoggedUserCanNotDeleteListings(): void
    {
        $client = static::createClient();

        $listing = $this->createListing($this->faker->realText(20), $this->faker->realText(50), $this->createUser());

        $client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertNotNull($this->repository->findOneBy([
            'slug' => $listing->getSlug()
        ]));
        $this->assertResponseRedirects('/login');
    }
}
