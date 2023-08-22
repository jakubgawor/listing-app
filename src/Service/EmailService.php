<?php

namespace App\Service;

use App\Entity\Listing;
use App\Entity\User;
use App\Message\SendEmailNotification;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\Config\AppConfig;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private MessageBusInterface $bus,
        private UserRepository      $userRepository,
        private Environment         $twig,
        private AppConfig           $appConfig,
        private EmailVerifier       $emailVerifier
    )
    {
    }

    public function notifyAdminAboutNewListing(string $slug): void
    {
        $admins = $this->userRepository->findAllAdmins();
        $adminEmails = array_map(function ($admin) {
            return $admin->getEmail();
        }, $admins);

        $message = $this->twig->render('_emails/_admin-listing-verification-email.html.twig', [
            'listingUrl' => $this->appConfig->getBaseUrl() . '/admin/listing/' . $slug,
            'verifyUrl' => $this->appConfig->getBaseUrl() . '/admin/listing/' . $slug . '/verify'
        ]);

        $this->bus->dispatch(new SendEmailNotification($adminEmails, 'New listing to verify!', $message));
    }

    public function notifyUserAboutListingVerification(User $user, Listing $listing): void
    {
        $message = $this->twig->render('_emails/_notification-about-listing-verification.html.twig', [
            'listingUrl' => $this->appConfig->getBaseUrl() . '/listing/' . $listing->getSlug()
        ]);

        $this->bus->dispatch(new SendEmailNotification([$user->getEmail()], 'Listing ' . $listing->getTitle() . ' has been verified!', $message));
    }

    public function sendRegistrationEmailConfirmation(User $user): void
    {
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('mailer@listing-app.com', 'Listing App'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}