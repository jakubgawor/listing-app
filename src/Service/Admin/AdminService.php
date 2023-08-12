<?php

namespace App\Service\Admin;

use App\Entity\Listing;
use App\Entity\User;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Exception\AdminDegradationException;
use App\Exception\AdminPromotionException;
use App\Exception\BanUserException;
use App\Exception\RepeatedVerificationException;
use App\Service\Email\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AdminService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly EmailService $emailService
    )
    {
    }

    public function verifyListing(Listing $listing): Listing
    {
        if ($listing->getStatus() === ListingStatusEnum::VERIFIED) {
            throw new RepeatedVerificationException('This listing is already verified!');
        }

        $this->entityManager->persist($listing->setStatus(ListingStatusEnum::VERIFIED));

        $this->emailService->notifyUserAboutListingVerification($listing->getBelongsToUser(), $listing);

        $this->entityManager->flush();

        return $listing;
    }

    public function promoteToAdmin(User $user): void
    {
        if(in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            throw new AdminPromotionException('You can not promote an admin!');
        }

        $this->entityManager->persist($user->setRoles([UserRoleEnum::ROLE_ADMIN]));
        $this->entityManager->flush();
    }

    public function degradeToUser(User $user): void
    {
        if ($user === $this->security->getUser()) {
            throw new AdminDegradationException('You can not degrade yourself!');
        }

        if (!in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            throw new AdminPromotionException('You can not degrade an user!');
        }

        $this->entityManager->persist($user->setRoles([UserRoleEnum::ROLE_USER_EMAIL_VERIFIED]));
        $this->entityManager->persist($user->setIsVerified(true));
        $this->entityManager->flush();
    }

    public function banUser(User $user): void
    {
        if ($user->isBanned() === true) {
            throw new BanUserException('User is already banned!');
        }

        if (in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            throw new BanUserException('You can not ban users with admin roles!');
        }

        foreach ($user->getListings()->toArray() as $listing) {
            $this->entityManager->remove($listing);
        }

        $this->entityManager->persist($user->setIsBanned(true));
        $this->entityManager->flush();
    }

    public function unbanUser(User $user): void
    {
        if (!$user->isBanned() === true) {
            throw new BanUserException('User is not banned');
        }

        $this->entityManager->persist($user->setIsBanned(false));
        $this->entityManager->flush();
    }


}