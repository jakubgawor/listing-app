<?php

namespace App\Tests\Controller\Listing;

use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Faker\Factory;
use Faker\Generator;

class RenderTest extends EntityBuilder
{
    private Generator $faker;

    public function setUp(): void
    {
        $this->faker = Factory::create();

        self::ensureKernelShutdown();
    }

    public function testRenderExistingVerifiedListing(): void
    {
        $client = static::createClient();
        $author = $this->createUser();
        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::VERIFIED, $author);

        $client->request('GET', '/listing/' . $listing->getSlug());

        $this->assertResponseIsSuccessful();
    }

    public function testDoNotRenderNotVerifiedListing(): void
    {
        $client = static::createClient();
        $author = $this->createUser();
        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::NOT_VERIFIED, $author);

        $client->request('GET', '/listing/' . $listing->getSlug());

        $this->assertResponseRedirects('/', 302);
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    public function testDoNotRenderNotExistingListing(): void
    {
        $client = static::createClient();

        $client->request('GET', '/listing/not-existing');

        $this->assertResponseRedirects('/', 302);
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    public function testCreateListingPageCanBeRenderedWhileUserIsVerifiedAndLoggedIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/create-listing');

        $this->assertResponseIsSuccessful();
    }

    public function testCreateListingPageCanNotBeRenderedWhileUserHasNotVerifiedEmailAddress(): void
    {
        $client = static::createClient();
        $user = $this->createUser([
            'role' => UserRoleEnum::ROLE_USER,
            'isVerified' => false
        ]);
        $client->loginUser($user);

        $client->request('GET', '/create-listing');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateListingPageCanNotBeRenderedWhileUserIsNotLoggedIn(): void
    {
        $client = static::createClient();

        $client->request('GET', '/create-listing');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/login');
    }

    public function testEditListingPageCanBeRenderedIfTheUserIsVerifiedAndIsOwnerOfTheVerifiedListing(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::VERIFIED, $user);
        $client->loginUser($user);

        $client->request('GET', '/listing/' . $listing->getSlug() . '/edit');

        $this->assertResponseIsSuccessful();
    }

    public function testEditListingPageCanNotBeRenderedIfTheListingIsNotVerified(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::NOT_VERIFIED, $user);
        $client->loginUser($user);

        $client->request('GET', '/listing/' . $listing->getSlug() . '/edit');

        $this->assertResponseRedirects('/', 302);
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    public function testEditListingPageCanNotBeRenderedIfTheUserIsNotTheOwnerOfTheListing(): void
    {
        $client = static::createClient();
        $owner = $this->createUser();
        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::VERIFIED, $owner);
        $client->loginUser($this->createUser());

        $client->request('GET', '/listing/' . $listing->getSlug() . '/edit');

        $this->assertResponseRedirects('/', 302);
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    public function testEditListingPageCanNotBeRenderedIfTheUserIsNotVerified(): void
    {
        $client = static::createClient();
        $user = $this->createUser([
            'role' => UserRoleEnum::ROLE_USER,
            'isVerified' => false
        ]);
        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::VERIFIED, $user);
        $client->loginUser($user);

        $client->request('GET', '/listing/' . $listing->getSlug() . '/edit');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditListingPageCanNotBeRenderedIfTheUserIsNotLoggedIn(): void
    {
        $client = static::createClient();
        $listing = $this->createListing($this->faker->realText(15), $this->faker->realText(20), ListingStatusEnum::VERIFIED, $this->createUser());

        $client->request('GET', '/listing/' . $listing->getSlug() . '/edit');

        $this->assertResponseRedirects('/login', 302);
    }

}
