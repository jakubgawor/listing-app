<?php

namespace App\Tests\functional\Controller\Admin;

use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;

class AdminIndexControllerTest extends EntityBuilder
{
    /** @test */
    public function index_renders_correctly()
    {
        $this->client->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
    }
}