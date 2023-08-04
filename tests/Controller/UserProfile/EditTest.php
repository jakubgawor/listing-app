<?php

namespace App\Tests\Controller\UserProfile;

use App\Entity\UserProfile;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;

class EditTest extends EntityBuilder
{
    private EntityRepository $repository;

    public function setUp(): void
    {
        $this->repository = static::getContainer()->get('doctrine')->getRepository(UserProfile::class);

        self::ensureKernelShutdown();
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
        $this->assertResponseRedirects('/user/' . $user->getUsername(), 302);
    }

    public function testUserCanNotEditProfileIfPhoneNumberExists(): void
    {
        $client = static::createClient();
        $phoneNumber = random_int(111111111, 999999999);
        $this->createUser(['phoneNumber' => $phoneNumber]);

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
            'id' => $user->getUserProfile()->getId(),
            'phone_number' => $phoneNumber
        ]));
    }

}
