<?php

namespace App\Tests\Controller\Admin\User;

use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class PromotionTest extends EntityBuilder
{
    public function testAdminCanPromoteUser(): void
    {
        $client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();

        $client->request('GET', '/admin/user/' . $user->getUsername() . '/promote');

        $this->assertContains(UserRoleEnum::ROLE_ADMIN, $user->getRoles());
    }

    public function testAdminCanNotPromoteHimself(): void
    {
        $client = static::createClient();
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $client->loginUser($admin);

        $client->request('GET', '/admin/user/' . $admin->getUsername() . '/promote');

        $this->assertResponseRedirects('/', 302);
        $this->assertNotNull($client->getRequest()->getSession()->getFlashBag()->get('notification'));
    }

    public function testAdminCanNotPromoteOtherAdmin(): void
    {
        $client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $otherAdmin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $client->request('GET', '/admin/user/' . $otherAdmin->getUsername() . '/promote');

        $this->assertResponseRedirects('/', 302);
        $this->assertNotNull($client->getRequest()->getSession()->getFlashBag()->get('notification'));
    }

    //todo
    public function testAdminCanNotPromoteNotExistingUser(): void
    {

    }
}
