<?php

namespace App\Tests\Controller;

use App\Entity\UserProfile;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserProfileControllerTest extends EntityBuilder
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function setUp(): void
    {
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(UserProfile::class);

        self::ensureKernelShutdown();
    }

    public function testUserProfilePageCanNotBeRenderedIfTheUserIsNotLoggedIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser();

        $client->request('GET', '/user/' . $user->getUsername());

        $this->assertNull($client->getRequest()->getUser());
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/login');
    }
    
    public function testUserProfilePageCanBeRenderedIfTheUserIsLoggedInAndHasVerifiedEmail(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/user/' . $user->getUsername());

        $this->assertResponseIsSuccessful();
    }

    public function testUserProfilePageCanBeRenderedIfTheUserIsLoggedInAndHasNotVerifiedEmail(): void
    {
        $client = static::createClient();
        $user = $this->createUser(null, UserRoleEnum::ROLE_USER, false);
        $client->loginUser($user);

        $client->request('GET', '/user/' . $user->getUsername());

        $this->assertResponseIsSuccessful();
    }

    public function testVerifiedUserCanEditProfile(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $phoneNumber = random_int(111111111, 999999999);

        $client->request('POST', '/user/' . $user->getUsername() . '/edit', [
            'user_profile_form' => [
                'first_name' => 'testName',
                'last_name' => 'testLastName',
                'phone_number' => $phoneNumber,
                'city' => 'testCity'
            ]
        ]);

        $userProfile = $this->repository->findOneBy([
            'phone_number' => $phoneNumber
        ]);

        $this->assertNotNull($userProfile);
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/user/' . $user->getUsername());
    }

    public function testUserCanNotEditProfileIfPhoneNumberExists(): void
    {
        $client = static::createClient();
        $phoneNumber = (string) random_int(111111111, 999999999);
        $this->createUser($phoneNumber);

        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('POST', '/user/' . $user->getUsername() . '/edit', [
            'user_profile_form' => [
                'first_name' => 'testName',
                'last_name' => 'testLastName',
                'phone_number' => $phoneNumber,
                'city' => 'testCity'
            ]
        ]);

        $this->assertNull($this->repository->findOneBy([
            'id' => $user->getId(),
            'phone_number' => $phoneNumber
        ]));
        $this->assertResponseStatusCodeSame(200);
    }

    public function testUserCanDeleteHisProfile(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/user/' . $user->getUsername() . '/delete');

        $this->assertNull($client->getRequest()->getUser());
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/');
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('success'));
    }

    public function testUserCanNotDeleteSomeoneElseProfile(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $someoneElse = $this->createUser();
        $client->request('GET', '/user/' . $someoneElse->getUsername() . '/delete');

        $this->assertNotNull($this->repository->findOneBy(['id' => $someoneElse->getUserProfile()->getId()]));
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/');
        $this->assertNotEmpty($client->getRequest()->getSession()->getFlashBag()->get('error'));
    }
}
