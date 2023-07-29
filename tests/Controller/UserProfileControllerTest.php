<?php

namespace App\Tests\Controller;

use App\Entity\UserProfile;
use App\Tests\Builder\EntityBuilder;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class UserProfileControllerTest extends EntityBuilder
{
    public function testUserProfilePageCanNotBeRenderedIfTheUserIsNotLoggedIn()
    {
        $client = static::createClient();
        $user = $this->createUser();

        $client->request('GET', '/user/' . $user->getUsername());

        $this->assertNull($client->getRequest()->getUser());
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/login');
    }
    
    public function testUserProfilePageCanBeRenderedIfTheUserIsLoggedIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/user/' . $user->getUsername());

        $this->assertResponseIsSuccessful();
    }

    public function testUserCanEditProfile()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
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

        $userProfile = $entityManager->getRepository(UserProfile::class)->findOneBy([
            'phone_number' => $phoneNumber
        ]);


        $this->assertNotNull($userProfile);
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertResponseRedirects('/user/' . $user->getUsername());
    }

    public function testUserCanNotEditProfileIfPhoneNumberExists()
    {
        $client = static::createClient();

        $phoneNumber = (string) random_int(111111111, 999999999);
        $this->createUser($phoneNumber);

        $session = new Session(new MockArraySessionStorage());

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

        $this->assertNotNull($session->getFlashBag()->get('verify.phone_number.error'));
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testUserCanDeleteHisProfile()
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/user/' . $user->getUsername() . '/delete');

        $this->assertNull($client->getRequest()->getUser());
        $this->assertResponseStatusCodeSame(302, $client->getResponse()->getStatusCode());
        $this->assertResponseRedirects('/');
    }
}
