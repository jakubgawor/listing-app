<?php

namespace App\Tests\Controller\Admin\Category;

use App\Entity\Category;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class EditTest extends EntityBuilder
{

    public function testAdminCanEditCategory(): void
    {
        $client = static::createClient();
        $repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Category::class);

        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $category = $this->createCategory(uniqid(), $admin);

        $newCategoryName = uniqid();

        $client
            ->loginUser($admin)
            ->request('GET', '/admin/category/' . $category->getId() . '/edit');

        $client->submitForm('Submit', [
            'category_form[category]' => $newCategoryName
        ]);

        $category = $repository->findOneBy(['category' => $newCategoryName]);

        $this->assertSame($newCategoryName, $category->getCategory());
        $this->assertResponseRedirects('/admin/categories', 302);
    }

    public function testAdminCanNotEditCategoryWithNameOfExistingCategory(): void
    {
        $client = static::createClient();

        $client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/category/not-existing/edit');

        $this->assertSame(['Object not found'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }
}