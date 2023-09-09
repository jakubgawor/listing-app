<?php

namespace App\Tests\integration\Service;

use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Message\SendEmailNotification;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\Config\AppConfig;
use App\Service\EmailService;
use App\Tests\Builder\EntityBuilder;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use Twig\Environment;
use Zenstruck\Messenger\Test\InteractsWithMessenger;


class EmailServiceTest extends EntityBuilder
{
    use InteractsWithMessenger;

    public function setUp(): void
    {
        parent::setUp();

        $this->transport('async')->reset();
    }

    /** @test */
    public function notifyAdminAboutNewListing_works_correctly()
    {
        $admin1 = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $admin2 = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findAllAdmins')->willReturn([$admin1, $admin2]);

        $bus = self::getContainer()->get(MessageBusInterface::class);
        $twig = self::getContainer()->get(Environment::class);
        $appConfig = self::getContainer()->get(AppConfig::class);
        $emailVerifier = self::getContainer()->get(EmailVerifier::class);
        $mailer = self::getContainer()->get(MailerInterface::class);

        $emailService = new EmailService(
            $bus,
            $userRepository,
            $twig,
            $appConfig,
            $emailVerifier,
            $mailer
        );

        $emailService->notifyAdminAboutNewListing('example-slug');

        $this->transport('async')->process();

        $this->transport('async')->queue()->messages(SendEmailNotification::class);
        $this->transport('async')->dispatched()->assertCount(2);
    }

    /** @test */
    public function notifyUserAboutListingVerification_works_correctly()
    {
        $user = $this->createUser(['role' => UserRoleEnum::ROLE_USER_EMAIL_VERIFIED]);
        $listing = $this->createListing(
            $this->faker->realText(15),
            $this->faker->realText(15),
            ListingStatusEnum::NOT_VERIFIED,
            $user,
            $this->createCategory(uniqid(), $this->createUser())
        );

        $userRepository = $this->createMock(UserRepository::class);
        $bus = self::getContainer()->get(MessageBusInterface::class);
        $twig = self::getContainer()->get(Environment::class);
        $appConfig = self::getContainer()->get(AppConfig::class);
        $emailVerifier = self::getContainer()->get(EmailVerifier::class);
        $mailer = self::getContainer()->get(MailerInterface::class);

        $emailService = new EmailService(
            $bus,
            $userRepository,
            $twig,
            $appConfig,
            $emailVerifier,
            $mailer
        );

        $emailService->notifyUserAboutListingVerification($user, $listing);

        $this->transport('async')->process();

        $this->transport('async')->dispatched()->assertNotEmpty();
        $this->transport('async')->queue()->messages(SendEmailNotification::class);
    }

    /** @test */
    public function sendRegistrationEmailConfirmation_works_correctly()
    {
        $user = $this->createUser();

        $userRepository = $this->createMock(UserRepository::class);
        $bus = self::getContainer()->get(MessageBusInterface::class);
        $twig = self::getContainer()->get(Environment::class);
        $appConfig = self::getContainer()->get(AppConfig::class);
        $emailVerifier = self::getContainer()->get(EmailVerifier::class);
        $mailer = self::getContainer()->get(MailerInterface::class);

        $emailService = new EmailService(
            $bus,
            $userRepository,
            $twig,
            $appConfig,
            $emailVerifier,
            $mailer
        );

        $emailService->sendRegistrationEmailConfirmation($user);

        $this->transport('async')->process();

        $this->transport('async')->dispatched()->assertNotEmpty();
        $this->transport('async')->dispatched()->messages(TemplatedEmail::class);
    }

    /** @test */
    public function sendPasswordResetEmail_works_correctly()
    {
        $user = $this->createUser();
        $resetPasswordToken = new ResetPasswordToken('example-token', new \DateTime('+1 day'), '15');


        $userRepository = $this->createMock(UserRepository::class);
        $bus = self::getContainer()->get(MessageBusInterface::class);
        $twig = self::getContainer()->get(Environment::class);
        $appConfig = self::getContainer()->get(AppConfig::class);
        $emailVerifier = self::getContainer()->get(EmailVerifier::class);
        $mailer = self::getContainer()->get(MailerInterface::class);

        $emailService = new EmailService(
            $bus,
            $userRepository,
            $twig,
            $appConfig,
            $emailVerifier,
            $mailer
        );

        $emailService->sendPasswordResetEmail($user, $resetPasswordToken);

        $this->transport('async')->process();

        $this->transport('async')->dispatched()->assertNotEmpty();
        $this->transport('async')->dispatched()->messages(TemplatedEmail::class);

    }
}