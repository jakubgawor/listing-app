<?php

namespace App\Tests\functional\Controller\Admin;

use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class AdminCategoryControllerTest extends EntityBuilder
{
    /** @test */
    public function categories_renders_correctly()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))->request('GET', '/admin/categories');

        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function addCategory_works_correctly()
    {
        $categoryName = uniqid();
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $this->client
            ->loginUser($admin)
            ->request('GET', '/admin/create-category');

        $this->client->submitForm('Submit', [
            'category_form[category]' => $categoryName
        ]);

        $category = $this->categoryRepository->findOneBy(['category' => $categoryName]);

        $this->assertNotNull($category);
        $this->assertSame($admin->getId(), $category->getAddedBy()->getId());
        $this->assertResponseRedirects('/admin/categories', 302);
    }

    /** @test */
    public function addCategory_when_category_name_exists()
    {
        $categoryName = uniqid();

        $this->createCategory($categoryName, $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));

        $this->client
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/create-category');

        $this->client->submitForm('Submit', [
            'category_form[category]' => $categoryName
        ]);

        $this->assertResponseIsUnprocessable();
    }

    /** @test */
    public function editCategory_works_correctly()
    {
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $category = $this->createCategory(uniqid(), $admin);

        $newCategoryName = uniqid();

        $this->client
            ->loginUser($admin)
            ->request('GET', '/admin/category/' . $category->getId() . '/edit');

        $this->client->submitForm('Submit', [
            'category_form[category]' => $newCategoryName
        ]);

        $category = $this->categoryRepository->findOneBy(['category' => $newCategoryName]);

        $this->assertSame($newCategoryName, $category->getCategory());
        $this->assertResponseRedirects('/admin/categories');
    }

    /** @test */
    public function editCategory_does_not_work_if_category_does_not_exist()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/category/not-existing/edit');

        $this->assertSame(['Object not found'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    /** @test */
    public function deleteCategory_works_correctly_and_deletes_associated_listings()
    {
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $listing = $this->createListing(
            'Delete associated listings',
            'Delete associated listings',
            ListingStatusEnum::VERIFIED,
            $this->createUser(),
            $this->createCategory(uniqid(), $admin)
        );

        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/category/' . $listing->getCategory()->getId() . '/delete');

        $this->assertNull($this->categoryRepository->findOneBy(['id' => $listing->getCategory()->getId()]));
        $this->assertNull($this->listingRepository->findOneBy(['id' => $listing->getId()]));
    }

    /** @test */
    public function deleteCategory_when_category_does_not_exist()
    {
        $this->client
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/category/not-existing/delete');

        $this->assertResponseRedirects('/');
        $this->assertSame(['Object not found'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

}