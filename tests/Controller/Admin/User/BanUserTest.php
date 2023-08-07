<?php

namespace App\Tests\Controller\Admin\User;

use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class BanUserTest extends EntityBuilder
{
    public function testAdminCanBanUser(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();

        $client->request('GET', '/admin/user/' . $user->getUsername() . '/ban');

        $this->assertSame(true, $user->isBanned());
    }

    public function testAdminCanBanUserAndTheListingsCreatedByUserWillBeDeleted(): void
    {
        $client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();
        $firstListing = $this->createListing('Title', 'Description', ListingStatusEnum::VERIFIED, $user);
        $secondListing = $this->createListing('Title', 'Description', ListingStatusEnum::VERIFIED, $user);

        $client->request('GET', '/admin/user/' . $user->getUsername() . '/ban');

        $this->assertNull($firstListing->getId());
        $this->assertNull($secondListing->getId());
        $this->assertSame(true, $user->isBanned());
    }

    public function testAdminCanNotBanBannedUser(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser(['isBanned' => true]);

        $client->request('GET', '/admin/user/' . $user->getUsername() . '/ban');

        $this->assertResponseStatusCodeSame(500);
    }

    public function testAdminCanNotBanOtherAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $otherAdmin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $client->request('GET', '/admin/user/' . $otherAdmin->getUsername() . '/ban');

        $this->assertResponseStatusCodeSame(500);
    }

    public function testAdminCanNotBanHimself(): void
    {
        $client = static::createClient();
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $client->loginUser($admin);

        $client->request('GET', '/admin/user/' . $admin->getUsername() . '/ban');

        $this->assertResponseStatusCodeSame(500);
    }

    public function testAdminCanNotBanNotExistingUser(): void
    {
        static::createClient()
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/user/not-existing/ban');

        $this->assertResponseRedirects('/', 302);
    }

}
