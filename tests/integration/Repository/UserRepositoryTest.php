<?php

namespace App\Tests\integration\Repository;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class UserRepositoryTest extends EntityBuilder
{
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