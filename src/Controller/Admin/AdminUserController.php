<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    #[Route('/admin/user/{username}/delete', name: 'app_admin_delete_user')]
    public function deleteUser(User $user, UserService $userService): Response
    {
        $userService->deleteUser($user);

        $this->addFlash('/', 'User has been deleted!');
        return $this->redirectToRoute('app_index');
    }


}
