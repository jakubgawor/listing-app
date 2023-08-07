<?php

namespace App\Tests\Controller\Admin\Listing;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class EditTest extends EntityBuilder
{
    private EntityRepository $repository;
    private Generator $faker;
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $this->repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Listing::class);
        $this->faker = Factory::create();

        self::ensureKernelShutdown();
    }

    public function testAdminCanEditSomeoneListing(): void
    {
        $client = $this->client;

        $author = $this->createUser();
        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::VERIFIED,
            $author);

        $oldSlug = $listing->getSlug();

        $title = $this->faker->realText(15);
        $description = $this->faker->realText(20);

        $crawler = $client->request('GET', '/admin/listing/' . $oldSlug . '/edit');
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
        $this->assertSame(ListingStatusEnum::VERIFIED, $editedListing->getStatus());
        $this->assertNotNull($editedListing);
        $this->assertNotNull($editedListing->getEditedAt());
    }

    public function testAdminCanNotEditNotExistingListing(): void
    {
        $client = $this->client;

        $client->request('GET', '/admin/listing/not-existing/edit');

        $this->assertSame(['Object not found'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }
}
