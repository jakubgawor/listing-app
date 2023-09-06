<?php

namespace App\Tests\functional\Controller\Auth;

use App\Tests\Builder\EntityBuilder;

class RegistrationControllerTest extends EntityBuilder
{
    /** @test */
    public function register_works_correctly_with_valid_form()
    {
        $uniqueId = uniqid();
        $this->createClientAndSubmitForm(
            'email' . $uniqueId . '@example.com',
            'username' . $uniqueId,
            '123456',
            '123456',
            true
        );

        $this->assertNotNull($this->userRepository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    /** @test */
    public function register_does_not_register_user_if_email_is_invalid()
    {
        $uniqueId = uniqid();
        $this->createClientAndSubmitForm(
            'email',
            'username' . $uniqueId,
            '123456',
            '123456',
            true
        );

        $this->assertNull($this->userRepository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    /** @test */
    public function register_does_not_register_user_if_password_is_invalid()
    {
        $uniqueId = uniqid();
        $this->createClientAndSubmitForm(
            'email' . $uniqueId . '@example.com',
            'username' . $uniqueId,
            '1',
            '1',
            true
        );

        $this->assertNull($this->userRepository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    /** @test */
    public function register_does_not_register_user_if_password_is_not_repeated()
    {
        $uniqueId = uniqid();
        $this->createClientAndSubmitForm(
            'email' . $uniqueId . '@example.com',
            'username' . $uniqueId,
            '123456',
            '654321',
            true
        );

        $this->assertNull($this->userRepository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    /** @test */
    public function register_does_not_register_user_if_repeated_password_is_invalid()
    {
        $uniqueId = uniqid();
        $this->createClientAndSubmitForm(
            'email' . $uniqueId . '@example.com',
            'username' . $uniqueId,
            '123456',
            '1',
            true
        );

        $this->assertNull($this->userRepository->findOneBy(['username' => 'username' . $uniqueId]));
    }

    private function createClientAndSubmitForm(
        string $email,
        string $username,
        string $plainPasswordFirst,
        string $plainPasswordSecond,
        bool $agreeTerms
    ): void
    {
        $this->client->submit(
            $this->client
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
