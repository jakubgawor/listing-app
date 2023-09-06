<?php

namespace App\Tests\functional\Controller\Admin\User;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Tests\Builder\EntityBuilder;
use Doctrine\ORM\EntityRepository;

class DeleteTest extends EntityBuilder
{
    private EntityRepository $repository;

    public function setUp(): void
    {
        $this->repository = static::getContainer()->get('doctrine')->getManager()->getRepository(User::class);

        self::ensureKernelShutdown();
    }

    public function testAdminCanDeleteUser(): void
    {
        $client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));
        $user = $this->createUser();

        $client->request('GET', '/admin/user/' . $user->getUsername() . '/delete');

        $this->assertNull($this->repository->findOneBy(['id' => $user->getId()]));
    }

    public function testAdminUserCanNotDeleteHisAccount(): void
    {
        $client = static::createClient();
        $admin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $client->loginUser($admin)->request('GET', '/admin/user/' . $admin->getUsername() . '/delete');

        $this->assertNotNull($this->repository->findOneBy(['id' => $admin->getId()]));
    }

    public function testAdminCanNotDeleteOtherAdminAccount(): void
    {
        $client = static::createClient();
        $otherAdmin = $this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]);

        $client
            ->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]))
            ->request('GET', '/admin/user/' . $otherAdmin->getUsername() . '/delete');

        $this->assertNotNull($this->repository->findOneBy(['id' => $otherAdmin->getId()]));
    }

    public function testAdminCanNotDeleteNotExistingUserAccount(): void
    {
        $client = static::createClient()->loginUser($this->createUser(['role' => UserRoleEnum::ROLE_ADMIN]));

        $client->request('GET', '/admin/user/not-existing/delete');

        $this->assertSame(['Object not found'], $client->getRequest()->getSession()->getFlashBag()->get('error'));
    }
}