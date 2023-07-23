<?php

namespace App\Tests\Builder;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Enum\UserRoleEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

interface UserBuilderInterface
{
    public function createUser(): User;
}
