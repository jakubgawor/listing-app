<?php

namespace App\Tests\Builder;

use App\Entity\User;

interface UserBuilderInterface
{
    public function createUser(): User;
}
