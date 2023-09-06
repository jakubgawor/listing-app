<?php

namespace App\Tests\functional\Controller\Admin\Listing;

use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class RenderTest extends EntityBuilder
{
    public function testShowListingsPageCanBeRendered(): void
    {
        static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))->request('GET', '/admin/listings');

        $this->assertResponseIsSuccessful();
    }

    public function testShowSingleVerifiedListingPageCanBeRenderedByAdmin(): void
    {
        $this->sendRequest(ListingStatusEnum::VERIFIED);

        $this->assertResponseIsSuccessful();
    }

    public function testShowSingleNotVerifiedListingPageCanBeRenderedByAdmin(): void
    {
        $this->sendRequest(ListingStatusEnum::NOT_VERIFIED);

        $this->assertResponseIsSuccessful();
    }

    public function testEditVerifiedListingPageCanBeRenderedByAdmin(): void
    {
        $this->sendRequest(ListingStatusEnum::VERIFIED, '/edit');

        $this->assertResponseIsSuccessful();
    }

    public function testEditNotVerifiedListingPageCanBeRenderedByAdmin(): void
    {
        $this->sendRequest(ListingStatusEnum::NOT_VERIFIED, '/edit');

        $this->assertResponseIsSuccessful();
    }

    private function sendRequest(string $listingStatus, ?string $additionalUri = null): void
    {
        static::createClient()
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/listing/' . $this->createListing(
                    'testListing',
                    'testListing',
                    $listingStatus,
                    $this->createUser(),
                    $this->createCategory(uniqid(), $this->createUser())
                )->getSlug() . $additionalUri
            );
    }

}
