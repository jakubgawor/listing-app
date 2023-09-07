<?php

namespace App\Tests\integration\Service;

use App\Enum\UserRoleEnum;
use App\Message\SendEmailNotification;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\Config\AppConfig;
use App\Service\EmailService;
use App\Tests\Builder\EntityBuilder;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;
use Zenstruck\Messenger\Test\InteractsWithMessenger;


class EmailServiceTest extends EntityBuilder
{
    use InteractsWithMessenger;

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



}