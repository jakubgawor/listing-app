<?php

namespace App\Tests\Controller\UserProfile;

use App\Entity\UserProfile;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;

class DeleteTest extends EntityBuilder
{
    private EntityRepository $repository;

    public function setUp(): void
    {
        $this->repository = static::getContainer()->get('doctrine')->getRepository(UserProfile::class);

        self::ensureKernelShutdown();
    }

    public function testUserCanDeleteHisProfile(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/user/' . $user->getUsername() . '/delete');

        $this->assertNull($this->repository->findOneBy(['id' => $user->getUserProfile()->getId()]));
    }

    public function testUserCanNotDeleteSomeoneElseProfile(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $someoneElse = $this->createUser();
        $client->request('GET', '/user/' . $someoneElse->getUsername() . '/delete');

        $this->assertNotNull($this->repository->findOneBy(['id' => $someoneElse->getUserProfile()->getId()]));
    }

    public function testAdminCanNotDeleteHisProfile(): void
    {
        $client = static::createClient();
        $user = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);
        $client->loginUser($user);

        $client->request('GET', '/user/' . $user->getUsername() . '/delete');

        $this->assertNotNull($this->repository->findOneBy(['id' => $user->getUserProfile()->getId()]));
    }

}
