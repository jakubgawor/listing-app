<?php

namespace App\Tests\Controller\Admin\Category;

use App\Entity\Category;
use App\Entity\Listing;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class DeleteTest extends EntityBuilder
{
    public function testAdminDeleteCategoryAndAssociatedListings(): void
    {
        $client = static::createClient();
        $categoryRepository = static::getContainer()->get('doctrine')->getManager()->getRepository(Category::class);
        $listingRepository = static::getContainer()->get('doctrine')->getManager()->getRepository(Listing::class);

        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $listing = $this->createListing(
            'Delete associated listings',
            'Delete associated listings',
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(uniqid(), $admin)
        );

        $client->loginUser($admin);

        $client->request('GET', '/admin/category/' . $listing->getCategory()->getId() . '/delete');

        $this->assertNull($categoryRepository->findOneBy(['id' => $listing->getCategory()->getId()]));
        $this->assertNull($listingRepository->findOneBy(['id' => $listing->getId()]));
    }

    public function testAdminCanNotDeleteNotExistingCategory(): void
    {
        $client = static::createClient();

        $client
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/category/not-existing/delete');

        $this->assertSame(['Object not found'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

}