<?php

namespace App\Tests\Base;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Enum\UserRoleEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class UserBaseTest extends WebTestCase
{
    protected function createUser(string $phoneNumber = null): User
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $uniqueId = uniqid();

        $userEmail = 'login_test_' . $uniqueId . '@login_test.com';
        $userUsername = 'login_test_' . $uniqueId;
        $userPassword = 'test_password';

        $user = new User;
        $user->setEmail($userEmail);
        $user->setUsername($userUsername);
        $user->setRoles([UserRoleEnum::ROLE_USER_EMAIL_VERIFIED]);
        $user->setIsVerified(true);
        $user->setPassword($passwordHasher->hashPassword($user, 'test_password'));
        $entityManager->persist($user);

        $userProfile = new UserProfile();
        $userProfile->setUser($user);
        $userProfile->setPhoneNumber($phoneNumber);
        $entityManager->persist($userProfile);

        $entityManager->flush();

        return $user;
    }

}
