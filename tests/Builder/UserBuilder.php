<?php

namespace App\Tests\Builder;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Enum\UserRoleEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class UserBuilder extends WebTestCase implements UserBuilderInterface
{
    public function createUser(string $phoneNumber = null): User
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
            ->setRoles([UserRoleEnum::ROLE_USER_EMAIL_VERIFIED])
            ->setIsVerified(true)
            ->setPassword($passwordHasher->hashPassword($user, $userPassword));

        $entityManager->persist($user);

        $userProfile = (new UserProfile)->setUser($user)->setPhoneNumber($phoneNumber);
        $entityManager->persist($userProfile);

        $entityManager->flush();

        return $user;
    }

}
