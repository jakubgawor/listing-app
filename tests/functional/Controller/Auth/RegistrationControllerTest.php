<?php

namespace App\Tests\functional\Controller\Auth;

use App\Entity\User;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;

class RegistrationControllerTest extends EntityBuilder
{
    private EntityRepository $repository;

    public function setUp(): void
    {
        $this->repository = static::getContainer()->get('doctrine')->getManager()->getRepository(User::class);

        self::ensureKernelShutdown();
    }

    public function testRegistrationPageCanBeRendered(): void
    {
        static::createClient()->request('GET', '/register');

        $this->assertResponseIsSuccessful();
    }

    public function testRegistrationPageCanNotBeRenderedWhileLoggedIn(): void
    {
        static::createClient()->loginUser($this->createUser())->request('GET', '/register');

        $this->assertResponseRedirects('/', 302);
    }

    public function testUserCanRegisterWithValidForm(): void
    {
        $uniqueId = uniqid();
        $this->createClientAndSubmitForm(
            'email' . $uniqueId . '@example.com',
            'username' . $uniqueId,
            '123456',
            '123456',
            true
        );

        $this->assertNotNull($this->repository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    public function testRegistrationWithInvalidEmail(): void
    {
        $uniqueId = uniqid();
        $this->createClientAndSubmitForm(
            'email',
            'username' . $uniqueId,
            '123456',
            '123456',
            true
        );

        $this->assertNull($this->repository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    public function testRegistrationWithInvalidPassword(): void
    {
        $uniqueId = uniqid();
        $this->createClientAndSubmitForm(
            'email' . $uniqueId . '@example.com',
            'username' . $uniqueId,
            '1',
            '1',
            true
        );

        $this->assertNull($this->repository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    public function testRegistrationWithNotRepeatedPassword(): void
    {
        $uniqueId = uniqid();
        $this->createClientAndSubmitForm(
            'email' . $uniqueId . '@example.com',
            'username' . $uniqueId,
            '123456',
            '654321',
            true
        );

        $this->assertNull($this->repository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    public function testRegistrationWithNotInvalidRepeatedPassword(): void
    {
        $uniqueId = uniqid();
        $this->createClientAndSubmitForm(
            'email' . $uniqueId . '@example.com',
            'username' . $uniqueId,
            '123456',
            '1',
            true
        );

        $this->assertNull($this->repository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    private function createClientAndSubmitForm(
        string $email,
        string $username,
        string $plainPasswordFirst,
        string $plainPasswordSecond,
        bool $agreeTerms
    ): void
    {
        $client = static::createClient();

        $client->submit(
            $client
                ->request('GET', '/register')
                ->selectButton('Register')
                ->form([
                    'registration_form[email]' => $email,
                    'registration_form[username]' => $username,
                    'registration_form[plainPassword][first]' => $plainPasswordFirst,
                    'registration_form[plainPassword][second]' => $plainPasswordSecond,
                    'registration_form[agreeTerms]' => $agreeTerms
                ])
        );
    }
}
