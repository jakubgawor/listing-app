<?php

namespace App\Tests\functional\Controller\UserProfile;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

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

        $this->editUserProfile($client, $user, $phoneNumber);

        $this->assertNotNull($this->repository->findOneBy(['phone_number' => $phoneNumber]));
        $this->assertResponseRedirects('/user/' . $user->getUsername(), 302);
    }

    public function testUserCanNotEditProfileIfPhoneNumberExists(): void
    {
        $client = static::createClient();
        $phoneNumber = random_int(111111111, 999999999);
        $this->createUser(['phoneNumber' => $phoneNumber]);

        $user = $this->createUser();
        $client->loginUser($user);

        $this->editUserProfile($client, $user, $phoneNumber);

        $this->assertNull($this->repository->findOneBy([
            'id' => $user->getUserProfile()->getId(),
            'phone_number' => $phoneNumber
        ]));
    }

    private function editUserProfile(KernelBrowser $client, User $user, string $phoneNumber): void
    {
        $crawler = $client->request('GET', '/user/' . $user->getUsername() . '/edit');
        $form = $crawler->selectButton('Update profile')->form([
            'user_profile_form[first_name]' => 'testName',
            'user_profile_form[last_name]' => 'testLastName',
            'user_profile_form[phone_number]' => $phoneNumber,
            'user_profile_form[city]' => 'testCity'
        ]);
        $client->submit($form);
    }

}
