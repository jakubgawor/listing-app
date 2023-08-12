<?php

namespace App\Tests\Controller\Admin\Category;

use App\Entity\Category;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class CreateTest extends EntityBuilder
{
    public function testAdminCanCreateNewCategory(): void
    {
        $client = static::createClient();
        $repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Category::class);

        $categoryName = uniqid();
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $client
            ->loginUser($admin)
            ->request('GET', '/admin/create-category');

        $client->submitForm('Submit', [
            'category_form[category]' => $categoryName
        ]);

        $category = $repository->findOneBy(['category' => $categoryName]);

        $this->assertNotNull($category);
        $this->assertSame($admin->getId(), $category->getAddedBy()->getId());
        $this->assertResponseRedirects('/admin/categories', 302);
    }

    public function testAdminCanNotCreateCategoryWithNameOfExistingCategory(): void
    {
        $client = static::createClient();

        $categoryName = uniqid();

        $this->createCategory($categoryName, $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));

        $client
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/create-category');

        $client->submitForm('Submit', [
            'category_form[category]' => $categoryName
        ]);

        $this->assertResponseIsUnprocessable();
    }
}