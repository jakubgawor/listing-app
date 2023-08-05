<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Exception\AdminDegradationException;
use App\Service\AdminService;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    public function __construct(
        private readonly UserService  $userService,
        private readonly AdminService $adminService
    )
    {
    }

    #[Route('/admin/user/{username}/delete', name: 'app_admin_delete_user')]
    public function deleteUser(User $user): Response
    {
        $this->userService->deleteUser($user);

        $this->addFlash('/', 'User has been deleted!');
        return $this->redirectToRoute('app_index');
    }

    #[Route('/admin/user/{username}/promote', name: 'app_admin_promote')]
    public function promoteToAdmin(User $user): Response
    {
        $this->adminService->promoteToAdmin($user);

        $this->addFlash('/', 'User has been promoted!');
        return $this->redirectToRoute('app_index');
    }

    #[Route('/admin/user/{username}/degrade', name: 'app_admin_degrade')]
    public function degradeToUser(User $user): Response
    {
        if ($user === $this->getUser()) {
            throw new AdminDegradationException('You can not degrade yourself!');
        }

        $this->adminService->degradeToUser($user);

        $this->addFlash('/', 'Admin has been degraded!');
        return $this->redirectToRoute('app_index');
    }

// todo
// ban user
// unban user
}
