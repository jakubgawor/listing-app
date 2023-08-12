<?php

namespace App\Tests\Controller\Admin\Category;

use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class RenderTest extends EntityBuilder
{
    public function testCreateCategoryPageCanBeRendered(): void
    {
        static::createClient()
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/create-category');

        $this->assertResponseIsSuccessful();
    }

}
