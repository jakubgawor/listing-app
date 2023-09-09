<?php

namespace App\Tests\functional\Controller;

use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class ListingControllerTest extends EntityBuilder
{
    /** @test */
    public function index_renders_correctly()
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function create_works_correctly()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $title = $this->faker->realText(10);
        $description = $this->faker->realText(15);

        $crawler = $this->client->request('GET', '/create-listing');

        $form = $crawler->selectButton('Submit')->form([
            'listing_form[title]' => $title,
            'listing_form[description]' => $description
        ]);
        $this->client->submit($form);

        /** @var Listing $listing */
        $listing = $this->listingRepository->findOneBy([
            'title' => $title,
            'description' => $description,
            'belongs_to_user' => $user
        ]);

        $this->assertNotNull($listing);
        $this->assertSame(ListingStatusEnum::NOT_VERIFIED, $listing->getStatus());
        $this->assertSame(null, $listing->getEditedAt());
    }

    /** @test */
    public function create_works_correctly_if_title_and_description_exist()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $title = $this->faker->realText(10);
        $description = $this->faker->realText(15);
        $category = $this->createCategory(uniqid(), $this->createUser());

        $existingListing = $this->createListing(
            $title,
            $description,
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $category
        );
        $listing = $this->createListing($title, $description, ListingStatusEnum::VERIFIED, $user, $category);

        $this->assertNotNull($this->listingRepository->findOneBy(['slug' => $listing->getSlug()]));
        $this->assertNotSame($existingListing->getSlug(), $listing->getSlug());
    }

    /** @test */
    public function edit_works_correctly()
    {
        $author = $this->createUser();
        $this->client->loginUser($author);

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

        $crawler = $this->client->request('GET', '/listing/' . $oldSlug . '/edit');
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
        $this->assertNotNull($editedListing);
        $this->assertSame(ListingStatusEnum::NOT_VERIFIED, $editedListing->getStatus());
        $this->assertNotNull($editedListing->getEditedAt());
    }

    /** @test */
    public function delete_works_correctly()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::VERIFIED,
            $user,
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertNull($this->listingRepository->findOneBy(['id' => $listing->getId()]));
    }

    /** @test */
    public function user_can_not_delete_not_verified_listing()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::NOT_VERIFIED,
            $user,
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertNotNull($this->listingRepository->findOneBy(['id' => $listing->getId()]));
    }

    /** @test */
    public function user_can_not_delete_someone_else_listing()
    {
        $author = $this->createUser();
        $this->client->loginUser($author);

        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertNotNull($this->listingRepository->findOneBy(['id' => $listing->getId()]));
    }

    /** @test */
    public function not_logged_user_can_not_delete_listings()
    {
        $listing = $this->createListing(
            $this->faker->realText(20),
            $this->faker->realText(50),
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client->request('GET', '/listing/' . $listing->getSlug() . '/delete');

        $this->assertResponseRedirects('/login', 302);
        $this->assertNotNull($this->listingRepository->findOneBy(['id' => $listing->getId()]));
    }

    /** @test */
    public function user_can_not_delete_not_existing_listing()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $this->client->request('GET', '/listing/not-existing/delete');

        $this->assertResponseRedirects('/', 302);
        $this->assertSame(['Object not found'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    /** @test */
    public function render_existing_verified_listing()
    {
        $author = $this->createUser();
        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::VERIFIED,
            $author,
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client->request('GET', '/listing/' . $listing->getSlug());

        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function do_not_render_not_verified_listing()
    {
        $author = $this->createUser();
        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::NOT_VERIFIED,
            $author,
            $this->createCategory(uniqid(), $this->createUser())
        );

        $this->client->request('GET', '/listing/' . $listing->getSlug());

        $this->assertResponseRedirects('/', 302);
    }

    /** @test */
    public function create_listing_page_can_be_rendered_if_user_is_verified()
    {
        $this->client
            ->loginUser($this->createUser())
            ->request('GET', '/create-listing');

        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function create_listing_page_can_not_be_rendered_if_user_has_not_verified_email_address()
    {
        $this->client
            ->loginUser($this->createUser([
                'role' => UserRoleEnum::ROLE_USER,
                'isVerified' => false
            ]))
            ->request('GET', '/create-listing');

        $this->assertResponseStatusCodeSame(403);
    }

    /** @test */
    public function edit_listing_page_can_be_rendered_if_the_user_is_verified_and_is_owner_of_the_listing()
    {
        $user = $this->createUser();
        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::VERIFIED,
            $user,
            $this->createCategory(uniqid(), $this->createUser())
        );
        $this->client->loginUser($user);

        $this->client->request('GET', '/listing/' . $listing->getSlug() . '/edit');

        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function edit_listing_page_can_not_be_rendered_if_the_listing_is_not_verified()
    {
        $user = $this->createUser();
        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::NOT_VERIFIED,
            $user,
            $this->createCategory(uniqid(), $this->createUser())
        );
        $this->client->loginUser($user);

        $this->client->request('GET', '/listing/' . $listing->getSlug() . '/edit');

        $this->assertResponseRedirects('/', 302);
    }

    /** @test */
    public function edit_listing_page_can_not_be_rendered_if_the_user_is_not_owner_of_the_listing()
    {
        $owner = $this->createUser();
        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::VERIFIED,
            $owner,
            $this->createCategory(uniqid(), $this->createUser())
        );
        $this->client->loginUser($this->createUser());

        $this->client->request('GET', '/listing/' . $listing->getSlug() . '/edit');

        $this->assertResponseRedirects('/', 302);
    }

    /** @test */
    public function edit_listing_page_can_not_be_rendered_if_the_user_is_not_verified()
    {
        $user = $this->createUser([
            'role' => UserRoleEnum::ROLE_USER,
            'isVerified' => false
        ]);
        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(20),
            ListingStatusEnum::VERIFIED,
            $user,
            $this->createCategory(uniqid(), $this->createUser())
        );
        $this->client->loginUser($user);

        $this->client->request('GET', '/listing/' . $listing->getSlug() . '/edit');

        $this->assertResponseStatusCodeSame(403);
    }

}