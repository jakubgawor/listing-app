<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AdminService;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserService    $userService,
        private readonly AdminService   $adminService
    )
    {
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function users(): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $this->userRepository->findAll()
        ]);
    }

    #[Route('/admin/user/{username}/delete', name: 'app_admin_delete_user')]
    public function deleteUser(?User $user): Response
    {
        $this->userService->deleteUser($user);

        $this->addFlash('success', 'User has been deleted!');
        return $this->redirectToRoute('app_index');
    }

    #[Route('/admin/user/{username}/promote', name: 'app_admin_promote')]
    public function promoteToAdmin(?User $user): Response
    {
        $this->adminService->promoteToAdmin($user);

        $this->addFlash('success', 'User has been promoted!');
        return $this->redirectToRoute('app_index');
    }

    #[Route('/admin/user/{username}/degrade', name: 'app_admin_degrade')]
    public function degradeToUser(?User $user): Response
    {
        $this->adminService->degradeToUser($user);

        $this->addFlash('success', 'Admin has been degraded!');
        return $this->redirectToRoute('app_index');
    }

    #[Route('/admin/user/{username}/ban', name: 'app_admin_ban')]
    public function banUser(?User $user): Response
    {
        $this->adminService->banUser($user);

        $this->addFlash('success', 'User ' . $user->getUsername() . ' has been banned!');
        return $this->redirectToRoute('app_index');
    }

    #[Route('/admin/user/{username}/unban', name: 'app_admin_unban')]
    public function unbanUser(?User $user): Response
    {
        $this->adminService->unbanUser($user);

        $this->addFlash('success', 'User ' . $user->getUsername() . ' has been unbanned!');
        return $this->redirectToRoute('app_index');
    }

}
