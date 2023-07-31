<?php

namespace App\Tests\Controller\Auth;

use App\Entity\User;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class RegistrationControllerTest extends EntityBuilder
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function setUp(): void
    {
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(User::class);

        self::ensureKernelShutdown();
    }

    public function testRegistrationPageCanBeRendered(): void
    {
        $client = static::createClient();

        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
    }

    public function testRegistrationPageCanNotBeRenderedWhileLoggedIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/register');

        $this->assertResponseRedirects('/');
        $this->assertResponseStatusCodeSame(302);
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    public function testUserCanRegisterWithValidForm(): void
    {
        $client = static::createClient();
        $uniqueId = uniqid();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'email' . $uniqueId . '@example.com',
            'registration_form[username]' => 'username' . $uniqueId,
            'registration_form[plainPassword][first]' => '123456',
            'registration_form[plainPassword][second]' => '123456',
            'registration_form[agreeTerms]' => '1'
        ]);
        $client->submit($form);

        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('notification'));
        $this->assertNotNull($this->repository->findOneBy(['username' => 'username' . $uniqueId]));
        $this->assertResponseRedirects('/');
        $this->assertResponseStatusCodeSame(302);
    }

    public function testRegistrationWithInvalidEmail(): void
    {
        $client = static::createClient();
        $uniqueId = uniqid();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'email',
            'registration_form[username]' => 'username' . $uniqueId,
            'registration_form[plainPassword][first]' => '123456',
            'registration_form[plainPassword][second]' => '123456',
            'registration_form[agreeTerms]' => '1'
        ]);
        $client->submit($form);

        $this->assertNull($this->repository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    public function testRegistrationWithInvalidPassword(): void
    {
        $client = static::createClient();
        $uniqueId = uniqid();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'email' . $uniqueId . '@example.com',
            'registration_form[username]' => 'username' . $uniqueId,
            'registration_form[plainPassword][first]' => '1',
            'registration_form[plainPassword][second]' => '1',
            'registration_form[agreeTerms]' => '1'
        ]);
        $client->submit($form);

        $this->assertNull($this->repository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    public function testRegistrationWithNotRepeatedPassword(): void
    {
        $client = static::createClient();
        $uniqueId = uniqid();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'email' . $uniqueId . '@example.com',
            'registration_form[username]' => 'username' . $uniqueId,
            'registration_form[plainPassword][first]' => '123456',
            'registration_form[plainPassword][second]' => '654321',
            'registration_form[agreeTerms]' => '1'
        ]);
        $client->submit($form);

        $this->assertNull($this->repository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    public function testRegistrationWithNotInvalidRepeatedPassword(): void
    {
        $client = static::createClient();
        $uniqueId = uniqid();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'email' . $uniqueId . '@example.com',
            'registration_form[username]' => 'username' . $uniqueId,
            'registration_form[plainPassword][first]' => '123456',
            'registration_form[plainPassword][second]' => '1',
            'registration_form[agreeTerms]' => '1'
        ]);
        $client->submit($form);

        $this->assertNull($this->repository->findOneBy(['username' => 'username' . $uniqueId]));
    }
}
