<?php

namespace App\Tests\Controller\Admin\Category;

use App\Entity\Category;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class CreateTest extends EntityBuilder
{
    private KernelBrowser $client;
    private EntityManager $entityManager;

    protected function setUp(): void
    {

        $this->client = static::createClient();

        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

    }

    public function testAdminCanCreateNewCategory(): void
    {
//        $repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Category::class);
        $repository = $this->entityManager->getRepository(Category::class);


        $categoryName = uniqid();
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $this->client
            ->loginUser($admin)
            ->request('GET', '/admin/create-category');

        $this->client->submitForm('Submit', [
            'category_form[category]' => $categoryName
        ]);

        $category = $repository->findOneBy(['category' => $categoryName]);

        $this->assertNotNull($category);
        $this->assertSame($admin->getId(), $category->getAddedBy()->getId());
        $this->assertResponseRedirects('/admin/categories', 302);
    }

    public function testAdminCanNotCreateCategoryWithNameOfExistingCategory(): void
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

}