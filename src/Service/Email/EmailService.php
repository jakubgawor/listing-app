<?php

namespace App\Service\Email;

use App\Entity\Listing;
use App\Entity\User;
use App\Message\SendEmailNotification;
use App\Repository\UserRepository;
use App\Service\Config\AppConfig;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private MessageBusInterface $bus,
        private UserRepository      $userRepository,
        private Environment         $twig,
        private AppConfig           $appConfig
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
}