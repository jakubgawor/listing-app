<?php

namespace App\Service;

use App\Entity\Listing;
use App\Entity\User;
use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Exception\BanUserException;
use App\Exception\UnauthorizedAccessException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ListingService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly EmailService $emailService
    )
    {
    }

    public function showOne(Listing $listing): Listing
    {
        if ($listing->getStatus() === ListingStatusEnum::NOT_VERIFIED) {
            throw new UnauthorizedAccessException('This listing is not verified.');
        }

        $this->entityManager->persist($listing->incrementViews());
        $this->entityManager->flush();

        return $listing;
    }

    public function create(Listing $listing, User $user): void
    {
        if($user->isBanned()) {
            throw new BanUserException('You are banned!');
        }

        if (in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            $this->entityManager->persist($listing->setStatus(ListingStatusEnum::VERIFIED));
        }

        $this->entityManager->persist($listing->setBelongsToUser($user));
        $this->emailService->notifyAdminAboutNewListing($listing->getSlug());

        $this->entityManager->flush();
    }

    public function edit(Listing $listing, User $user): void
    {
        if (!in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            $this->entityManager->persist($listing->setStatus(ListingStatusEnum::NOT_VERIFIED));
        }

        if (in_array(UserRoleEnum::ROLE_ADMIN, $this->security->getUser()->getRoles())) {
            $this->entityManager->persist($listing->setStatus(ListingStatusEnum::VERIFIED));
        }

        $this->entityManager->persist($listing->setEditedAt(new DateTime));
        $this->entityManager->flush();
    }

    public function deleteListing(Listing $listing): void
    {
        $this->entityManager->remove($listing);
        $this->entityManager->flush();
    }


}