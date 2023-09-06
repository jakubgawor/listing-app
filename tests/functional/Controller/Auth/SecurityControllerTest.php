<?php

namespace App\Tests\functional\Controller\Auth;

use App\Tests\Builder\EntityBuilder;

class SecurityControllerTest extends EntityBuilder
{
    public function testLoginPageCanBeRenderedWhileUserIsNotLoggedIn(): void
    {
        $this->sendGetRequest('/login');

        $this->assertResponseIsSuccessful();
    }

    public function testLoginPageCanNotBeRenderedWhileUserIsLoggedIn(): void
    {
        $this->loginAndSendGetRequest('/login');

        $this->assertResponseRedirects('/', 302);
    }

    public function testUserCanLogOut(): void
    {
        $this->loginAndSendGetRequest('/logout');

        $this->assertResponseStatusCodeSame(302);
    }

    public function testUserCanNotLogOutWhileNotLoggedIn(): void
    {
        $this->sendGetRequest('/logout');

        $this->assertResponseStatusCodeSame(302);
    }

    public function testUserCanNotRenderLoginPageWhileLoggedIn(): void
    {
        $this->loginAndSendGetRequest('/login');

        $this->assertResponseRedirects('/', 302);
    }

    private function loginAndSendGetRequest(string $uri): void
    {
        static::createClient()->loginUser($this->createUser())->request('GET', $uri);
    }

    private function sendGetRequest(string $uri): void
    {
        static::createClient()->request('GET', $uri);
    }

}
