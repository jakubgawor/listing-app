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

class VerifyTest extends EntityBuilder
{
    private Generator $faker;
    private EntityRepository $repository;
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Listing::class);
        $this->faker = Factory::create();

        self::ensureKernelShutdown();
    }

    public function testAdminCanVerifyNotVerifiedListing(): void
    {
        $client = $this->client;
        $listing = $this->listing(ListingStatusEnum::NOT_VERIFIED);

        $client->request('GET', '/admin/listing/' . $listing->getSlug() . '/verify');

        $this->assertResponseRedirects('/listing/' . $listing->getSlug());
        $this->assertSame(ListingStatusEnum::VERIFIED, $this->repository->findOneBy(['slug' => $listing->getSlug()])->getStatus());
    }

    public function testAdminCanNotVerifyVerifiedListing(): void
    {
        $client = $this->client;
        $listing = $this->listing(ListingStatusEnum::VERIFIED);

        $client->request('GET', '/admin/listing/' . $listing->getSlug() . '/verify');

        $this->assertSame(['This listing is already verified!'], $client->getRequest()->getSession()->getFlashBag()->get('notification'));
    }

    public function testAdminCanNotVerifyNotExistingListing(): void
    {
        $client = $this->client;

        $client->request('GET', '/admin/listing/not-existing/verify');

        $this->assertSame(['Object not found'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    private function listing(string $status): Listing
    {
        return $this->createListing(
            $this->faker->realText(10),
            $this->faker->realText(15),
            $status,
            $this->createUser()
        );
    }

}
