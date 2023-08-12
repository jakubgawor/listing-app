<?php

namespace App\Tests\Controller\Admin\Category;

use App\Entity\Category;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;

class EditTest extends EntityBuilder
{
    private EntityRepository $repository;

    public function setUp(): void
    {
        $this->repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Category::class);

        self::ensureKernelShutdown();
    }

    public function testAdminCanEditCategory(): void
    {
        $client = static::createClient();

        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $category = $this->createCategory(uniqid(), $admin);

        $newCategoryName = uniqid();

        $client
            ->loginUser($admin)
            ->request('GET', '/admin/category/' . $category->getId() . '/edit');

        $client->submitForm('Submit', [
            'category_form[category]' => $newCategoryName
        ]);

        $category = $this->repository->findOneBy(['category' => $newCategoryName]);

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