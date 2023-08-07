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

    public function testUserCanRenderSomeoneElseProfile(): void
    {
        static::createClient()
            ->loginUser($this->createUser())
            ->request('GET', '/user/' . $this->createUser()->getUsername());

        $this->assertResponseIsSuccessful();
    }

    public function testUserCanNotEditSomeoneElseProfile(): void
    {
        static::createClient()
            ->loginUser($this->createUser())
            ->request('GET', '/user/' . $this->createUser()->getUsername() . '/edit');

        $this->assertResponseRedirects('/', 302);
    }

    public function testUserCanNotDeleteNotExistingProfile(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/user/not-existing/delete');

        $this->assertSame(['Object not found'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }
}
