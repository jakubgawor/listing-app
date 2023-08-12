<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Service\Authorization\AuthorizationService;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
        private readonly UserService $userService
    )
    {
    }

    #[Route('/user/{username}/delete', name: 'app_user_profile_delete', methods: 'GET')]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function delete(?User $user): Response
    {
        $this->authorizationService->denyUnauthorizedUserAccess($user);
        $this->userService->deleteUser($user);

        $this->addFlash('success', 'Your profile has been deleted!');
        return $this->redirectToRoute('app_index');
    }
}