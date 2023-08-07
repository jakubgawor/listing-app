<?php

namespace App\Tests\Controller\Admin\User;

use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class DegradationTest extends EntityBuilder
{

    public function testAdminCanDegradeOtherAdmin(): void
    {
        $client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $otherAdmin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $client->request('GET', '/admin/user/' . $otherAdmin->getUsername() . '/degrade');

        $this->assertNotContains(UserRoleEnum::ROLE_ADMIN, $otherAdmin->getRoles());
    }

    public function testAdminCanNotDegradeUser(): void
    {
        $client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();

        $client->request('GET', '/admin/user/' . $user->getUsername() . '/degrade');

        $this->assertSame(['You can not degrade an user!'], $client->getRequest()->getSession()->getFlashBag()->get('notification'));
    }

    public function testAdminCanNotDegradeHimself(): void
    {
        $client = static::createClient();
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $client->loginUser($admin);

        $client->request('GET', '/admin/user/' . $admin->getUsername() . '/degrade');

        $this->assertContains(UserRoleEnum::ROLE_ADMIN, $admin->getRoles());
    }

    public function testAdminCanNotDegradeNotExistingUser(): void
    {
        $client = static::createClient();
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $client->loginUser($admin);

        $client->request('GET', '/admin/user/not-existing/degrade');

        $this->assertSame(['Object not found'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }
}
