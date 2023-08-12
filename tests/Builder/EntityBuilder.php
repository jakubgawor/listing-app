<?php

namespace App\Tests\Builder;

use App\Entity\Category;
use App\Entity\Listing;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Enum\UserRoleEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class EntityBuilder extends WebTestCase implements EntityBuilderInterface
{
    public function createUser(array $data = []): User
    {
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $uniqueId = uniqid();

        $userData = [
            'email' => 'login_test_' . $uniqueId . '@login_test.com',
            'username' => 'login_test_' . $uniqueId,
            'phoneNumber' => null,
            'password' => 'test_password',
            'role' => UserRoleEnum::ROLE_USER_EMAIL_VERIFIED,
            'isVerified' => true,
            'isBanned' => false,
        ];

        foreach ($userData as $key => $value) {
            $userData[$key] = $data[$key] ?? $value;
        }

        $user = new User;
        $user
            ->setEmail($userData['email'])
            ->setUsername($userData['username'])
            ->setRoles([$userData['role']])
            ->setIsVerified($userData['isVerified'])
            ->setPassword($passwordHasher->hashPassword($user, $userData['password']))
            ->setIsBanned($userData['isBanned'])
            ->setUserProfile((new UserProfile)->setUser($user)->setPhoneNumber($userData['phoneNumber']));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function createListing(string $title, string $description, string $status, User $user, Category $category): Listing
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $listing = new Listing;
        $listing
            ->setTitle($title)
            ->setDescription($description)
            ->setStatus($status)
            ->setBelongsToUser($user)
            ->setCategory($category);

        $entityManager->persist($user->addListing($listing));
        $entityManager->persist($listing);
        $entityManager->flush();

        return $listing;
    }

    public function createCategory(string $categoryName, User $addedBy): Category
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $category = (new Category)
            ->setCategory($categoryName)
            ->setAddedBy($addedBy);

        $entityManager->persist($addedBy->addCategory($category));
        $entityManager->persist($category);
        $entityManager->flush();

        return $category;
    }
}
