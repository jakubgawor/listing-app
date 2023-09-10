<?php

namespace App\Tests\functional\Controller\Admin;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Utils\EntityBuilder;

class AdminListingControllerTest extends EntityBuilder
{
    /** @test */
    public function showListings_renders_correctly()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))->request('GET', '/admin/listings');

        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function showListing_renders_correctly()
    {
        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(15),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))->request('GET', '/admin/listing/' . $listing->getSlug());

        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function verify_works_correctly_if_listing_is_not_verified()
    {
        $listing = $this->createListing(
            $this->faker->realText(10),
            $this->faker->realText(15),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client
            ->request('GET', '/admin/listing/' . $listing->getSlug() . '/verify');

        $this->assertSame(ListingStatusEnum::VERIFIED, $this->listingRepository->findOneBy(['slug' => $listing->getSlug()])->getStatus());
    }

    /** @test */
    public function verify_redirects_if_listing_is_verified()
    {
        $listing = $this->createListing(
            $this->faker->realText(10),
            $this->faker->realText(15),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/listing/' . $listing->getSlug() . '/verify');

        $this->assertSame(['This listing is already verified!'], $this->client->getRequest()->getSession()->getFlashBag()->get('notification'));
    }

    /** @test */
    public function verify_redirects_if_listing_does_not_exist()
    {
        $this->client
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/listing/not-existing/verify');

        $this->assertSame(['Object not found'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    /** @test */
    public function edit_works_correctly()
    {
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
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/listing/' . $oldSlug . '/edit');
        $form = $crawler->selectButton('Edit listing')->form([
            'listing_form[title]' => $title,
            'listing_form[description]' => $description
        ]);
        $this->client->submit($form);

        /** @var Listing $editedListing */
        $editedListing = $this->listingRepository->findOneBy([
            'title' => $title,
            'description' => $description,
            'belongs_to_user' => $author->getId()
        ]);

        $this->assertNull($this->listingRepository->findOneBy(['slug' => $oldSlug]));
        $this->assertSame(ListingStatusEnum::VERIFIED, $editedListing->getStatus());
        $this->assertNotNull($editedListing);
        $this->assertNotNull($editedListing->getEditedAt());
    }

    /** @test */
    public function delete_works_correctly()
    {
        $listing = $this->createListing(
            $this->faker->realText(10),
            $this->faker->realText(15),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/listing/' . $listing->getSlug() . '/delete');

        $this->assertNull($this->listingRepository->findOneBy(['slug' => $listing->getSlug()]));
    }
}