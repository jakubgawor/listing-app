<?php

namespace App\Tests\functional\Controller\Auth;

use App\Tests\Builder\EntityBuilder;

class SecurityControllerTest extends EntityBuilder
{
    /** @test */
    public function logout_works_correctly(): void
    {
        $this->client->loginUser($this->createUser())->request('GET', '/logout');

        $this->assertResponseStatusCodeSame(302);
    }

    public function testUserCanNotLogOutWhileNotLoggedIn(): void
    {
        $this->client->loginUser($this->createUser())->request('GET', '/logout');

        $this->assertResponseStatusCodeSame(302);
    }

    public function testUserCanNotRenderLoginPageWhileLoggedIn(): void
    {
        $this->client->loginUser($this->createUser())->request('GET', '/login');

        $this->assertResponseRedirects('/', 302);
    }
}
