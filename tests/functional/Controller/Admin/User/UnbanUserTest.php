<?php

namespace App\Tests\functional\Controller\Admin\User;

use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class UnbanUserTest extends EntityBuilder
{
    public function testAdminCanUnbanUser(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser(['isBanned' => true]);

        $client->request('GET', '/admin/user/' . $user->getUsername() . '/unban');

        $this->assertSame(false, $user->isBanned());
    }

    public function testAdminCanNotUnbanNotBannedUser(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser(['isBanned' => false]);

        $client->request('GET', '/admin/user/' . $user->getUsername() . '/unban');

        $this->assertSame(['User is not banned'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    public function testAdminCanNotUnbanNotExistingUser(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/user/not-existing/ban');

        $this->assertSame(['Object not found'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

}
