<?php

namespace App\Tests\functional\Controller;

use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class AccountControllerTest extends EntityBuilder
{
    /** @test */
    public function verified_user_can_edit_profile()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);
        $phoneNumber = random_int(111111111, 999999999);

        $crawler = $this->client->request('GET', '/user/' . $user->getUsername() . '/edit');
        $form = $crawler->selectButton('Update profile')->form([
            'user_profile_form[first_name]' => 'testName',
            'user_profile_form[last_name]' => 'testLastName',
            'user_profile_form[phone_number]' => $phoneNumber,
            'user_profile_form[city]' => 'testCity'
        ]);
        $this->client->submit($form);

        $this->assertNotNull($this->userProfileRepository->findOneBy(['phone_number' => $phoneNumber]));
        $this->assertResponseRedirects('/user/' . $user->getUsername(), 302);
    }

    /** @test */
    public function user_can_not_edit_profile_if_phone_number_exists()
    {
        $phoneNumber = random_int(111111111, 999999999);
        $this->createUser(['phoneNumber' => $phoneNumber]);

        $user = $this->createUser();
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/user/' . $user->getUsername() . '/edit');
        $form = $crawler->selectButton('Update profile')->form([
            'user_profile_form[first_name]' => 'testName',
            'user_profile_form[last_name]' => 'testLastName',
            'user_profile_form[phone_number]' => $phoneNumber,
            'user_profile_form[city]' => 'testCity'
        ]);
        $this->client->submit($form);

        $this->assertNull($this->userProfileRepository->findOneBy([
            'id' => $user->getUserProfile()->getId(),
            'phone_number' => $phoneNumber
        ]));
    }

    /** @test */
    public function user_can_delete_his_account()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $this->client->request('GET', '/user/' . $user->getUsername() . '/delete');

        $this->assertNull($this->userRepository->findOneBy(['id' => $user->getUserProfile()->getId()]));
    }

    /** @test */
    public function user_can_not_delete_someone_else_account()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $someoneElse = $this->createUser();
        $this->client->request('GET', '/user/' . $someoneElse->getUsername() . '/delete');

        $this->assertNotNull($this->userRepository->findOneBy(['id' => $someoneElse->getUserProfile()->getId()]));
    }

    /** @test */
    public function admin_can_not_delete_his_account()
    {
        $user = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $this->client->loginUser($user);

        $this->client->request('GET', '/user/' . $user->getUsername() . '/delete');

        $this->assertNotNull($this->userRepository->findOneBy(['id' => $user->getUserProfile()->getId()]));
    }

    /** @test */
    public function user_profile_page_can_be_rendered_if_the_user_is_logged_in()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $this->client->request('GET', '/user/' . $user->getUsername());

        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function user_profile_page_can_not_be_rendered_if_the_user_is_not_logged_in()
    {
        $user = $this->createUser();

        $this->client->request('GET', '/user/' . $user->getUsername());

        $this->assertResponseRedirects('/login', 302);
    }

    /** @test */
    public function user_can_not_edit_someone_else_profile()
    {
        $this->client
            ->loginUser($this->createUser())
            ->request('GET', '/user/' . $this->createUser()->getUsername() . '/edit');

        $this->assertResponseRedirects('/', 302);
    }

}