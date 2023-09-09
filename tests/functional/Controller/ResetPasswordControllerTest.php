<?php

namespace App\Tests\functional\Controller;

use App\Entity\ResetPasswordRequest;
use App\Tests\Builder\EntityBuilder;

class ResetPasswordControllerTest extends EntityBuilder
{
    private $resetPasswordRequestRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->resetPasswordRequestRepository = $this->entityManager->getRepository(ResetPasswordRequest::class);
    }

    /** @test */
    public function request_works_correctly()
    {
        $user = $this->createUser();

        $this->client->submit(
            $this->client
                ->request('GET', '/reset-password')
                ->selectButton('Send password reset email')
                ->form([
                    'reset_password_request_form[email]' => $user->getEmail()
                ])
        );

        $this->assertSame($user->getId(), $this->resetPasswordRequestRepository->findOneBy(['user' => $user])->getUser()->getId());
        $this->assertResponseRedirects('/reset-password/check-email');
    }

    /** @test */
    public function request_if_user_was_not_found()
    {
        $this->client->submit(
            $this->client
                ->request('GET', '/reset-password')
                ->selectButton('Send password reset email')
                ->form([
                    'reset_password_request_form[email]' => 'exampleRequestEmail@example.com'
                ])
        );

        $this->assertResponseRedirects('/reset-password/check-email');
    }

}