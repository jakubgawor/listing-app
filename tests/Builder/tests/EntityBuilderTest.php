<?php

namespace App\Tests\Builder\tests;

use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Faker\Factory;
use Faker\Generator;

class EntityBuilderTest extends EntityBuilder
{
    private Generator $faker;

    public function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function testCreateUserBuilderByDefaultData(): void
    {
        $user = $this->createUser();

        $this->assertNotNull($user);
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

        $user = $this->createUser($data);

        $this->assertSame($phoneNumber, $user->getUserProfile()->getPhoneNumber());
        $this->assertNotNull($user);
    }

    public function testCreateListingBuilder(): void
    {
        $author = $this->createUser();

        $title = $this->faker->realText(15);
        $description = $this->faker->realText(20);
        $status = ListingStatusEnum::NOT_VERIFIED;
        $category = $this->createCategory($this->faker->realText(30), $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));

        $listing = $this->createListing($title, $description, $status, $author, $category);

        $this->assertNotNull($listing);
        $this->assertSame($status, $listing->getStatus());
        $this->assertSame(null, $listing->getEditedAt());
        $this->assertSame($author->getId(), $listing->getBelongsToUser()->getId());
        $this->assertSame($category->getCategory(), $listing->getCategory()->getCategory());
    }

    public function testCreateCategoryBuilder(): void
    {
        $categoryName = $this->faker->realText(30);
        $createdBy = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $category = $this->createCategory($categoryName, $createdBy);

        $this->assertNotNull($category);
        $this->assertSame($createdBy->getId(), $category->getAddedBy()->getId());
    }
}