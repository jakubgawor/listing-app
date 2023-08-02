<?php

namespace App\Tests\Builder\tests;

use App\Entity\Listing;
use App\Entity\User;
use App\Enum\ListingStatusEnum;
use App\Repository\ListingRepository;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Faker\Factory;
use Faker\Generator;

class EntityBuilderTest extends EntityBuilder
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $userRepository;
    private ListingRepository $listingRepository;
    private Generator $faker;

    public function setUp(): void
    {
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->listingRepository = $this->entityManager->getRepository(Listing::class);
        $this->faker = Factory::create();
    }

    public function testCreateUserBuilderByDefaultData(): void
    {
        $user = $this->createUser();

        /** @var User $createdUser */
        $createdUser = $this->userRepository->findOneBy([
            'id' => $user->getId(),
            'username' => $user->getUsername()
        ]);

        $this->assertNotNull($createdUser);
    }

    public function testCreateUserBuilderByCustomData(): void
    {
        $uniqueId = uniqid();
        $phoneNumber = (string) random_int(111111111, 999999999);

        $data = [
            'username' => 'customUsername' . $uniqueId,
            'email' => 'customEmail' . $uniqueId . '@example.com',
            'phoneNumber' => $phoneNumber
        ];

        $this->createUser($data);

        /** @var User $createdUser */
        $user = $this->userRepository->findOneBy([
            'username' => 'customUsername' . $uniqueId,
            'email' => 'customEmail' . $uniqueId . '@example.com',
        ]);

        $this->assertSame($phoneNumber, $user->getUserProfile()->getPhoneNumber());
        $this->assertNotNull($user);
    }

    public function testCreateListingBuilder(): void
    {
        $author = $this->createUser();

        $title = $this->faker->realText(15);
        $description = $this->faker->realText(20);
        $status = ListingStatusEnum::NOT_VERIFIED;

        $slug = $this->createListing($title, $description, $status, $author)->getSlug();

        /** @var Listing $listing */
        $listing = $this->listingRepository->findOneBy([
            'slug' => $slug
        ]);

        $this->assertNotNull($listing);
        $this->assertSame($status, $listing->getStatus());
        $this->assertSame(null, $listing->getEditedAt());
        $this->assertSame($author->getId(), $listing->getBelongsToUser()->getId());
    }
}