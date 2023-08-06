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

        $this->assertContains(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED, $otherAdmin->getRoles());
        $this->assertNotContains(UserRoleEnum::ROLE_ADMIN, $otherAdmin->getRoles());
        $this->assertResponseRedirects('/', 302);
    }

    public function testAdminCanNotDegradeUser(): void
    {
        $client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();

        $client->request('GET', '/admin/user/' . $user->getUsername() . '/degrade');

        $this->assertResponseRedirects('/', 302);
    }

    public function testAdminCanNotDegradeHimself(): void
    {
        $client = static::createClient();
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $client->loginUser($admin);

        $client->request('GET', '/admin/user/' . $admin->getUsername() . '/degrade');

        $this->assertContains(UserRoleEnum::ROLE_ADMIN, $admin->getRoles());
        $this->assertResponseRedirects('/', 302);
    }

}
