<?php

namespace App\Tests\integration\Repository;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Tests\Utils\EntityBuilder;

class UserRepositoryTest extends EntityBuilder
{
    /** @test */
    public function upgradePassword_works_correctly()
    {
        $user = $this->createUser();
        $oldPassword = $user->getPassword();

        $newHashedPassword = password_hash('new_password', PASSWORD_BCRYPT);

        $this->userRepository->upgradePassword($user, $newHashedPassword);

        $this->assertNotSame($oldPassword, $user->getPassword());
    }

    /** @test */
    public function findAllAdmins_works_correctly()
    {
        $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $admins = $this->userRepository->findAllAdmins();

        $this->assertContainsOnlyInstancesOf(User::class, $admins);
        $this->assertTrue(in_array(UserRoleEnum::ROLE_ADMIN, $admins[0]->getRoles()));
        $this->assertNotEmpty($admins);
    }

}