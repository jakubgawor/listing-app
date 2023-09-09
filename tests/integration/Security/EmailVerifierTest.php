<?php

namespace App\Tests\integration\Security;

use App\Security\EmailVerifier;
use App\Tests\Builder\EntityBuilder;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifierTest extends EntityBuilder
{
    private MailerInterface $mailer;

    public function setUp(): void
    {
        parent::setUp();

        $this->mailer = self::getContainer()->get(MailerInterface::class);
    }

    /** @test */
    public function sendEmailConfirmation_works_correctly()
    {
        $user = $this->createUser();

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Confirm Your Email')
            ->htmlTemplate('registration/confirmation_email.html.twig');

        $emailVerifier = new EmailVerifier(
            self::getContainer()->get(VerifyEmailHelperInterface::class),
            $this->mailer,
            $this->entityManager
        );

        $emailVerifier->sendEmailConfirmation('app_verify_email', $user, $email);

        $messages = $this->getMailerMessages();

        $this->assertCount(1, $messages);
        $this->assertInstanceOf(TemplatedEmail::class, $messages[0]);
    }

    /** @test */
    public function handleEmailConfirmation_works_correctly()
    {
        $user = $this->createUser();
        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturn('mocked_uri');

        $verifyEmailHelper = $this->createMock(VerifyEmailHelperInterface::class);

        $emailVerifier = new EmailVerifier(
          $verifyEmailHelper,
          $this->mailer,
          $this->entityManager
        );

        $emailVerifier->handleEmailConfirmation($request, $user);

        $this->assertTrue($user->isVerified());
    }
}