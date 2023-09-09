<?php

namespace App\Tests\functional\Controller\Admin;

use App\Enum\ListingStatusEnum;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class AdminUserControllerTest extends EntityBuilder
{
    /** @test */
    public function users_render_correctly()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))->request('GET', '/admin/users');

        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function deleteUser_works_correctly()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();

        $this->client->request('GET', '/admin/user/' . $user->getUsername() . '/delete');

        $this->assertNull($this->userRepository->findOneBy(['id' => $user->getId()]));
    }

    /** @test */
    public function deleteUser_does_not_work_if_admin_is_trying_to_delete_his_account()
    {
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $this->client->loginUser($admin)->request('GET', '/admin/user/' . $admin->getUsername() . '/delete');

        $this->assertNotNull($this->userRepository->findOneBy(['id' => $admin->getId()]));
    }

    /** @test */
    public function deleteUser_does_not_work_if_user_is_admin()
    {
        $otherAdmin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $this->client
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/user/' . $otherAdmin->getUsername() . '/delete');

        $this->assertNotNull($this->userRepository->findOneBy(['id' => $otherAdmin->getId()]));
    }

    /** @test */
    public function deleteUser_redirects_if_user_does_not_exist()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));

        $this->client->request('GET', '/admin/user/not-existing/delete');

        $this->assertResponseRedirects('/');
        $this->assertSame(['Object not found'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    /** @test */
    public function promoteToAdmin_works_correctly()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();

        $this->client->request('GET', '/admin/user/' . $user->getUsername() . '/promote');

        $this->assertContains(UserRoleEnum::ROLE_ADMIN, $user->getRoles());
    }

    /** @test */
    public function promoteToAdmin_redirects_if_admin_is_trying_to_promote_himself()
    {
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/user/' . $admin->getUsername() . '/promote');

        $this->assertResponseRedirects('/');
        $this->assertSame(['You can not promote an admin!'], $this->client->getRequest()->getSession()->getFlashBag()->get('notification'));
    }

    /** @test */
    public function promoteToAdmin_redirects_if_admin_is_trying_to_promote_other_admin()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $otherAdmin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $this->client->request('GET', '/admin/user/' . $otherAdmin->getUsername() . '/promote');

        $this->assertResponseRedirects('/');
        $this->assertSame(['You can not promote an admin!'], $this->client->getRequest()->getSession()->getFlashBag()->get('notification'));
    }

    /** @test */
    public function promoteToAdmin_redirects_if_user_does_not_exist()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));

        $this->client->request('GET', '/admin/user/not-existing/promote');

        $this->assertResponseRedirects('/');
        $this->assertSame(['Object not found'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    /** @test */
    public function degradeToUser_works_correctly()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $otherAdmin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $this->client->request('GET', '/admin/user/' . $otherAdmin->getUsername() . '/degrade');

        $this->assertNotContains(UserRoleEnum::ROLE_ADMIN, $otherAdmin->getRoles());
    }

    /** @test */
    public function degradeToUser_redirects_if_admin_is_trying_to_degrade_user()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();

        $this->client->request('GET', '/admin/user/' . $user->getUsername() . '/degrade');

        $this->assertResponseRedirects('/');
        $this->assertSame(['You can not degrade an user!'], $this->client->getRequest()->getSession()->getFlashBag()->get('notification'));
    }

    /** @test */
    public function degradeToUser_does_not_work_if_admin_is_trying_to_degrade_himself()
    {
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/user/' . $admin->getUsername() . '/degrade');

        $this->assertContains(UserRoleEnum::ROLE_ADMIN, $admin->getRoles());
    }

    /** @test */
    public function degradeToUser_redirects_if_admin_is_trying_to_degrade_not_existing_user()
    {
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/user/not-existing/degrade');

        $this->assertResponseRedirects('/');
        $this->assertSame(['Object not found'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    /** @test */
    public function banUser_works_correctly_if_user_has_not_listings()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();

        $this->client->request('GET', '/admin/user/' . $user->getUsername() . '/ban');

        $this->assertSame(true, $user->isBanned());
    }

    /** @test */
    public function banUser_works_correctly_and_delete_associated_listings()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();

        $category = $this->createCategory(uniqid(), $this->createUser());
        $firstListing = $this->createListing('Title', 'Description', ListingStatusEnum::VERIFIED, $user, $category);
        $secondListing = $this->createListing('Title', 'Description', ListingStatusEnum::VERIFIED, $user, $category);

        $this->client->request('GET', '/admin/user/' . $user->getUsername() . '/ban');

        $this->assertNull($firstListing->getId());
        $this->assertNull($secondListing->getId());
        $this->assertSame(true, $user->isBanned());
    }

    /** @test */
    public function banUser_redirects_if_admin_is_trying_to_ban_banned_user()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser(['isBanned' => true]);

        $this->client->request('GET', '/admin/user/' . $user->getUsername() . '/ban');

        $this->assertResponseRedirects('/');
        $this->assertSame(['User is already banned!'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    /** @test */
    public function banUser_does_not_ban_other_admin()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $otherAdmin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $this->client->request('GET', '/admin/user/' . $otherAdmin->getUsername() . '/ban');

        $this->assertSame(false, $otherAdmin->isBanned());
    }

    /** @test */
    public function banUser_admin_can_not_ban_himself()
    {
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/user/' . $admin->getUsername() . '/ban');

        $this->assertSame(false, $admin->isBanned());
    }

    /** @test */
    public function banUser_redirects_if_user_is_not_existing()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));

        $this->client->request('GET', '/admin/user/not-existing/ban');

        $this->assertResponseRedirects('/');
        $this->assertSame(['Object not found'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    /** @test */
    public function unbanUser_works_correctly()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser(['isBanned' => true]);

        $this->client->request('GET', '/admin/user/' . $user->getUsername() . '/unban');

        $this->assertSame(false, $user->isBanned());
    }

    /** @test */
    public function unbanUser_redirects_if_user_is_not_banned()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser(['isBanned' => false]);

        $this->client->request('GET', '/admin/user/' . $user->getUsername() . '/unban');

        $this->assertResponseRedirects('/');
        $this->assertSame(['User is not banned'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }

    /** @test */
    public function unbanUser_redirects_if_user_does_not_exist()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/user/not-existing/ban');

        $this->assertResponseRedirects('/');
        $this->assertSame(['Object not found'], $this->client->getRequest()->getSession()->getFlashBag()->get('error'));
    }
}