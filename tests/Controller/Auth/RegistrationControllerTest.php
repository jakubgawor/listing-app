<?php

namespace App\Tests\Controller\Auth;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

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

    public function testRegistrationWithInvalidEmail(): void
    {
        $client = static::createClient();

        $session = new Session(new MockArraySessionStorage());
        $request = Request::createFromGlobals();
        $request->setSession($session);

        $uniqueId = uniqid();

        $crawler = $client->request('POST', '/register', [
            'registration_form' => [
                'email' => 'invalid_email',
                'username' => 'test_username' . $uniqueId,
                'plainPassword' => [
                    'first' => 'test_password' . $uniqueId,
                    'second' => 'test_password' . $uniqueId
                ],
                'agreeTerms' => '1',
            ]]);


        $this->assertNotNull($session->getFlashBag()->get('verify_email_error'));
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
