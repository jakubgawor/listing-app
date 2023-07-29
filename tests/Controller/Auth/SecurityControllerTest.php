<?php

namespace App\Tests\Controller\Auth;

use App\Tests\Builder\EntityBuilder;

class SecurityControllerTest extends EntityBuilder
{
    public function testUserCanLogInWithTheCorrectData(): void
    {
        $client = static::createClient();

        $user = $this->createUser();

        $crawler = $client->request('POST', '/login', [
            '_username' => $user->getUsername(),
            '_password' => 'test_password'
        ]);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    public function testUserEntersWrongPassword(): void
    {
        $client = static::createClient();

        $user = $this->createUser();

        $crawler = $client->request('POST', '/login', [
            '_username' => $user->getUsername(),
            '_password' => 'wrong_password'
        ]);

        $this->assertNull($client->getRequest()->getUser());
    }

    public function testUserEntersWrongUsername(): void
    {
        $client = static::createClient();

        $this->createUser();

        $crawler = $client->request('POST', '/login', [
            '_username' => 'wrong_username',
            '_password' => 'test_password'
        ]);

        $this->assertNull($client->getRequest()->getUser());
    }

}
