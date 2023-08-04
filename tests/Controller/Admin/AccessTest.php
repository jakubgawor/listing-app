<?php

namespace App\Tests\Controller\Admin;

use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class AccessTest extends EntityBuilder
{
    public function testAdminCanRenderAdminPage(): void
    {
        static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
    }

    public function testNotLoggedUserCanNotRenderAdminPage(): void
    {
        static::createClient()->request('GET', '/admin');

        $this->assertResponseRedirects('/login', 302);
    }

    public function testLoggedVerifiedUserCanNotRenderAdminPage(): void
    {
        static::createClient()->loginUser($this->createUser())->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testLoggedNotVerifiedUserCanNotRenderAdminPage(): void
    {
        static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_USER, 'isVerified' => false]))->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(403);
    }
}
