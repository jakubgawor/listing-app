<?php

namespace App\Tests\Controller\Auth;

use App\Tests\Builder\EntityBuilder;

class SecurityControllerTest extends EntityBuilder
{
    public function testLoginPageCanBeRenderedWhileUserIsNotLoggedIn(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
    }

    public function testLoginPageCanNotBeRenderedWhileUserIsLoggedIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/login');

        $this->assertResponseRedirects('/');
        $this->assertResponseStatusCodeSame(302);
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    public function testUserCanLogOut(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/logout');

        $this->assertResponseStatusCodeSame(302);
    }

    public function testUserCanNotLogOutWhileNotLoggedIn(): void
    {
        $client = static::createClient();

        $client->request('GET', '/logout');

        $this->assertResponseStatusCodeSame(302);
    }

    public function testUserCanNotRenderLoginPageWhileLoggedIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser();

        $client->loginUser($user);

        $client->request('GET', '/login');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/');
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

}
