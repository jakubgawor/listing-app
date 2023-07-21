<?php

namespace App\Tests\Auth;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegistrationPageCanBeRendered(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
    }

    public function testUserCanRegisterWithValidForm()
    {
        $client = static::createClient();

        $uniqueId = uniqid();

        $client->request('POST', '/register', [
            'registration_form' => [
                'email' => 'test_email_' . $uniqueId . '@example.com',
                'username' => 'test_username' . $uniqueId,
                'plainPassword' => [
                    'first' => 'test_password' . $uniqueId,
                    'second' => 'test_password' . $uniqueId
                ],
                'agreeTerms' => '1',
            ]]);


        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $user = $entityManager->getRepository(User::class)->findOneBy([
            'email' => 'test_email_' . $uniqueId . '@example.com',
            'username' => 'test_username' . $uniqueId
        ]);

        $this->assertNotNull($user);
    }
}
