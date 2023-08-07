<?php

namespace App\Tests\Controller\Admin\User;

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

        $this->assertResponseStatusCodeSame(500);
    }

    public function testAdminCanNotUnbanNotExistingUser(): void
    {
        static::createClient()
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/user/not-existing/ban');

        $this->assertResponseRedirects('/', 302);
    }

}
