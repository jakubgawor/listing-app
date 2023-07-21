<?php

namespace App\Tests\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegistrationPageCanBeRendered(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());


//        $this->assertResponseIsSuccessful();
    }
}
