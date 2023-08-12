<?php

namespace App\Tests\Controller\Admin\Category;

use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class RenderTest extends EntityBuilder
{
    public function testCreateCategoryPageCanBeRendered(): void
    {
        $this->loginAndRequest('/admin/create-category');

        $this->assertResponseIsSuccessful();
    }

    public function testEditCategoryPageCanBeRenderedIfCategoryExists(): void
    {
        $client = static::createClient();

        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $category = $this->createCategory(uniqid(), $admin);

        $client
            ->loginUser($admin)
            ->request('GET', '/admin/category/' . $category->getId() . '/edit');

        $this->assertResponseIsSuccessful();
    }

    public function testEditCategoryPageCanNotBeRenderedIfCategoryDoesNotExist(): void
    {
        $client = $this->loginAndRequest('/admin/category/not-existing/edit');

        $this->assertSame(['Object not found'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    private function loginAndRequest(string $uri): KernelBrowser
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', $uri);

        return $client;
    }

}
