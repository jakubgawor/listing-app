<?php

namespace App\Tests\Controller\UserProfile;

use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class AccessTest extends EntityBuilder
{
    public function testUserProfilePageCanBeRenderedIfTheUserIsLoggedInAndHasVerifiedEmail(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/user/' . $user->getUsername());

        $this->assertResponseIsSuccessful();
    }

    public function testUserProfilePageCanBeRenderedIfTheUserIsLoggedInAndHasNotVerifiedEmail(): void
    {
        $client = static::createClient();
        $user = $this->createUser([
            'role' => UserRoleEnum::ROLE_USER,
            'isVerified' => false
        ]);
        $client->loginUser($user);

        $client->request('GET', '/user/' . $user->getUsername());

        $this->assertResponseIsSuccessful();
    }

    public function testUserProfilePageCanNotBeRenderedIfTheUserIsNotLoggedIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser();

        $client->request('GET', '/user/' . $user->getUsername());

        $this->assertResponseRedirects('/login', 302);
    }

}
