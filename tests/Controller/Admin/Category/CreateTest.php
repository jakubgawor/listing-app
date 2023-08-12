<?php

namespace App\Tests\Controller\Admin\Category;

use App\Entity\Category;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;
use Faker\Factory;
use Faker\Generator;

class CreateTest extends EntityBuilder
{
    private Generator $faker;
    private EntityRepository $repository;

    public function setUp(): void
    {
        $this->faker = Factory::create();
        $this->repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Category::class);

        self::ensureKernelShutdown();
    }

    public function testAdminCanCreateNewCategory(): void
    {
        $client = static::createClient();

        $categoryName = $this->faker->realText(10) . uniqid();
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $client
            ->loginUser($admin)
            ->request('GET', '/admin/create-category');

        $client->submitForm('Submit', [
            'category_form[category]' => $categoryName
        ]);

        $category = $this->repository->findOneBy(['category' => $categoryName]);

        $this->assertNotNull($category);
        $this->assertSame($admin->getId(), $category->getAddedBy()->getId());
        $this->assertResponseRedirects('/admin/categories', 302);
    }

    public function testAdminCanNotCreateCategoryWithNameOfExistingCategory(): void
    {
        $client = static::createClient();

        $categoryName = $this->faker->realText(10);

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