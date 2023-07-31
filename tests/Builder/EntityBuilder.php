<?php

namespace App\Tests\Builder;

use App\Entity\Listing;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Enum\UserRoleEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class EntityBuilder extends WebTestCase implements EntityBuilderInterface
{
    public function createUser(string $phoneNumber = null, string $role = UserRoleEnum::ROLE_USER_EMAIL_VERIFIED, bool $isVerfified = true): User
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $uniqueId = uniqid();

        $userEmail = 'login_test_' . $uniqueId . '@login_test.com';
        $userUsername = 'login_test_' . $uniqueId;
        $userPassword = 'test_password';

        $user = new User;
        $user
            ->setEmail($userEmail)
            ->setUsername($userUsername)
            ->setRoles([$role])
            ->setIsVerified($isVerfified)
            ->setPassword($passwordHasher->hashPassword($user, $userPassword));

        $entityManager->persist($user);

        $userProfile = (new UserProfile)->setUser($user)->setPhoneNumber($phoneNumber);
        $entityManager->persist($userProfile);

        $entityManager->flush();

        return $user;
    }

    public function createListing(string $title, string $description, User $user): Listing
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $listing = new Listing;
        $listing
            ->setTitle($title)
            ->setDescription($description)
            ->setBelongsToUser($user);

        $entityManager->persist($listing);
        $entityManager->flush();

        return $listing;
    }
}
