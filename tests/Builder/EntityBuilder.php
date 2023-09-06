<?php

namespace App\Tests\Builder;

use App\Entity\Category;
use App\Entity\Listing;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Enum\UserRoleEnum;
use App\Repository\CategoryRepository;
use App\Repository\ListingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EntityBuilder extends WebTestCase implements EntityBuilderInterface
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;
    protected ListingRepository $listingRepository;
    protected CategoryRepository $categoryRepository;
    protected UserRepository $userRepository;
    protected Generator $faker;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->listingRepository = $this->entityManager->getRepository(Listing::class);
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);

        $this->faker = Factory::create();
    }

    public function createUser(array $data = []): User
    {
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
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

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function createListing(string $title, string $description, string $status, User $user, Category $category): Listing
    {
        $listing = new Listing;
        $listing
            ->setTitle($title)
            ->setDescription($description)
            ->setStatus($status)
            ->setBelongsToUser($user)
            ->setCategory($category);

        $this->entityManager->persist($user->addListing($listing));
        $this->entityManager->persist($listing);
        $this->entityManager->flush();

        return $listing;
    }

    public function createCategory(string $categoryName, User $addedBy): Category
    {
        $category = (new Category)
            ->setCategory($categoryName)
            ->setAddedBy($addedBy);

        $this->entityManager->persist($addedBy->addCategory($category));
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }
}
