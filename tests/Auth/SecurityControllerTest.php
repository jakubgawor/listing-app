<?php

namespace App\Tests\Auth;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
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


    protected function createUser(): User
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $uniqueId = uniqid();

        $userEmail = 'login_test_' . $uniqueId . '@login_test.com';
        $userUsername = 'login_test_' . $uniqueId;
        $userPassword = 'test_password';

        $user = new User;
        $user->setEmail($userEmail);
        $user->setUsername($userUsername);
        $user->setPassword($passwordHasher->hashPassword($user, 'test_password'));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
