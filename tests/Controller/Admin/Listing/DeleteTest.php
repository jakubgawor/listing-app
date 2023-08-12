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

class DeleteTest extends EntityBuilder
{
    private Generator $faker;
    private EntityRepository $repository;
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $this->repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Listing::class);
        $this->faker = Factory::create();

        self::ensureKernelShutdown();
    }

    public function testAdminCanDeleteSomeoneElseListing(): void
    {
        $listing = $this->createListing(
            $this->faker->realText(10),
            $this->faker->realText(15),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client->request('GET', '/admin/listing/' . $listing->getSlug() . '/delete');

        $this->assertNull($this->repository->findOneBy(['slug' => $listing->getSlug()]));
    }

    public function testAdminCanNotDeleteNotExistingListing(): void
    {
        $client = $this->client;

        $client->request('GET', '/admin/listing/not-existing/delete');

        $this->assertSame(['Object not found'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }
}
